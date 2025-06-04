<div>

  <div class="card">
    <div class="card-header">
      <h5 class="fw-bold">Staffs</h5>
      <button wire:click="createModal" class="btn btn-primary">Add Staff</button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table responsive-table table-striped table-bordered">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Name</th>
              <th scope="col">Email</th>
              <th scope="col">Role</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($admins as $admin)
              <tr>
                <td data-label="@lang('ID')">{{ $loop->iteration }}</td>
                <td data-label="@lang('Name')">{{ $admin->name }}</td>
                <td data-label="@lang('Email')">{{ $admin->email }}</td>
                <td data-label="@lang('Role')"> <span class="badge bg-info"> {{ $admin->role }}</span></td>
                <td data-label="@lang('action')">
                  <button wire:click="edit('{{ $admin->id }}')" class="btn btn-sm btn-primary">Edit</button>
                  @if (auth('admin')->user()->role == 'super' && $admin->role != 'super')
                    <button wire:click="delete('{{ $admin->id }}')" class="btn btn-sm btn-danger">Delete</button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center">No admins found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div wire:ignore.self class="modal fade" id="adminFormModal" tabindex="-1" role="dialog"
    aria-labelledby="adminFormModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form wire:submit.prevent="save" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="adminFormModalLabel">
            {{ $editingId ? 'Edit Staff' : 'Add Staff' }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          </button>
        </div>

        <div class="modal-body row">
          <div class="form-group col-md-6">
            <label class="form-label">@lang('Full Name')</label>
            <input type="text" wire:model.defer="name" class="form-control" required>
            @error('name')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group col-md-6">
            <label class="form-label">@lang('Email Address')</label>
            <input type="email" wire:model.defer="email" class="form-control" required>
            @error('email')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group col-md-6">
            <label class="form-label">@lang('Phone')</label>
            <input type="text" wire:model.defer="phone" class="form-control">
            @error('phone')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group col-md-6">
            <label class="form-label">Password</label>
            <input type="password" wire:model.defer="password" class="form-control"
              placeholder="Leave empty if not updating">
            @error('password')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>

          <button type="submit" class="w-100 btn btn-success">
            {{ $editingId ? 'Update' : 'Create' }} Admin
          </button>
        </div>
      </form>
    </div>
  </div>

</div>

@assets
  <script>
    window.addEventListener('show-form', () => {
      const modal = new bootstrap.Modal(document.getElementById('adminFormModal'));
      modal.show();
    });

    window.addEventListener('hide-form', () => {
      const modal = bootstrap.Modal.getInstance(document.getElementById('adminFormModal'));
      modal.hide();
    });

  </script>
@endassets
