@extends('admin.layouts.master')

@section('title', 'Manage Categories')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="fw-bold">Contests Categories</h5>
        <button class="btn-primary btn" data-bs-toggle="modal" data-bs-target="#createCategory">Add Category</button>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped responsive-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $item)
                    <tr id="category-{{ $item->id }}">
                        <td class="row-number" data-label="@lang('#')">
                            {{$loop->iteration}}
                        </td>
                        <td data-label="@lang('Name')">
                            {{$item->name}}
                        </td>
                        <td data-label="@lang('Status')">
                           <span class="badge py-0 @if ($item->status == 'enabled') bg-success @else bg-danger @endif">{{ $item->status }}</span>
                        </td>
                        <td data-label="@lang('Actions')">
                            <button class="btn btn-secondary btn-sm" onclick="openEditModal({{ $item }})"><i class="fad fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteCategory('{{ $item->id }}')"><i class="fad fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name</label>
                        <input type="text" id="editName" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-select">
                            <option value="enabled">Enabled</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Create Modal --}}
<div class="modal fade" id="createCategory" tabindex="-1" aria-labelledby="createCategoryLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createCategoryForm" method="POST">
                @csrf
                @method('POST')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Create Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openEditModal(category) {
        $('#editName').val(category.name);
        $('#editId').val(category.id);
        $('#editStatus').val(category.status);

        const form = $('#editForm');
        form.attr('action', `{{ route('admin.category.update', '') }}/${category.id}`);

        const editModal = new bootstrap.Modal($('#editModal')[0]);
        editModal.show();
    }

    function deleteCategory(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('admin.category.delete', '') }}/" + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        document.getElementById('category-' + id).remove();
                        toastr.success('Category has been deleted.');
                        updateRowNumbers();
                    } else {
                        throw new Error('Failed to delete');
                    }
                })
                .catch(error => {
                    toastr.error('Failed to delete category.')
                });
            }
        });
    }

    function updateRowNumbers() {
        $('tbody tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
        });
    }

    $('#createCategoryForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        $.ajax({
            url: "{{ route('admin.category.store') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                JDLoader.open();
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                JDLoader.close();

                if (response.status === 'success') {
                    const category = response.category;

                    const lastRow = $('tbody tr').last();
                    const newRowNumber = lastRow.find('.row-number').length ? parseInt(lastRow.find('.row-number').text()) + 1 : 1;

                    const row = `
                        <tr id="category-${category.id}">
                            <td class="row-number" data-label="@lang('#')">${newRowNumber}</td>
                            <td data-label="@lang('Name')">${category.name}</td>
                            <td data-label="@lang('Status')">
                                <span class="badge py-0 bg-success">enabled</span>
                            </td>
                            <td data-label="@lang('Actions')">
                                <button class="btn btn-secondary btn-sm" onclick="openEditModal(${JSON.stringify(category)})">
                                    <i class="fad fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteCategory('${category.id}')">
                                    <i class="fad fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    $('tbody').append(row);
                    $('#createCategoryForm')[0].reset(); // Reset the form
                    $('#createCategory').modal('hide');
                    toastr.success('Category created successfully');
                    updateRowNumbers();
                } else {
                    toastr.error(response.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                JDLoader.close();
                const errorMessage = xhr.responseJSON?.message || 'Failed to create category';
                toastr.error(errorMessage);
            }
        });
    });

    $('#editForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const categoryId = formData.get('id');

        $.ajax({
            url: "{{ route('admin.category.update', '') }}/" + categoryId,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                JDLoader.open();
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                JDLoader.close();

                if (response.status === 'success') {
                    const category = response.category;
                    const row = $('#category-' + category.id);

                    row.find('[data-label="@lang('Name')"]').text(category.name);
                    row.find('[data-label="@lang('Status')"] .badge')
                        .removeClass('bg-success bg-danger')
                        .addClass(category.status === 'enabled' ? 'bg-success' : 'bg-danger')
                        .text(category.status);

                    $('#editModal').modal('hide');
                    toastr.success('Category updated successfully');
                } else {
                    toastr.error(response.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                JDLoader.close();
                const errorMessage = xhr.responseJSON?.message || 'Failed to update category';
                toastr.error(errorMessage);
            }
        });
    });
</script>
@endpush

