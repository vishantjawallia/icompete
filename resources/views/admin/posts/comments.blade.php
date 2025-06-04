@extends('admin.layouts.master')

@section('title', 'Posts Comments')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="fw-bold">Posts Comments</h5>
        {{-- search  --}}
        <form action="" method="GET">
            <div class="input-group justify-content-end">
                <input type="search" name="search" class="form-control" placeholder="@lang('Search comments')" value="{{request()->search ?? ''}}" id="search">
                <button class="btn btn-primary input-group-text" type="submit"><i class="far fa-search"></i></button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div id="comments-container">
            @forelse ($comments as $comment)
            <div class="comment-item mb-3" id="comment-{{ $comment->id }}">
                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="{{ my_asset($comment->user->image) }}" alt="" class="rounded-circle" width="40">
                        <div class="ms-2 ">
                            <h6 class="mb-0"><a href="{{route('admin.users.view', $comment->user_id)}}" title="View User">{{ $comment->user->full_name }}</a> </h6>
                            <small class="text-muted">{{ show_datetime($comment->created_at) }}</small>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn bg-light text-primary border border-primary p-1" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item edit-comment" href="javascript:void(0)" data-id="{{ $comment->id }}" data-content="{{ $comment->content }}">Edit</a></li>
                            <li><a class="dropdown-item delete-comment" href="javascript:void(0)" data-id="{{ $comment->id }}">Delete</a></li>
                        </ul>
                        <a class="btn btn-sm btn-outline-primary p-1" title="view post" href="{{ route('admin.community.view', $comment->post_id) }}">
                            <i class="bi bi-file-text"></i>Post
                        </a>
                    </div>
                </div>

                <div class="comment-content mt-2" id="comment-content-{{ $comment->id }}">
                    <p class="mb-0 text-dark">{{ $comment->content }}</p>
                </div>
                <div class="comment-edit-form d-none" id="comment-edit-{{ $comment->id }}">
                    <textarea class="form-control mb-2" rows="3">{{ $comment->content }}</textarea>
                    <button class="btn btn-sm btn-primary save-comment" data-id="{{ $comment->id }}">Save</button>
                    <button class="btn btn-sm btn-secondary cancel-edit" data-id="{{ $comment->id }}">Cancel</button>
                </div>
            </div>
            @empty
            <p class="text-center fs-16"><i class="fa fa-face-sad-tear"></i> No comments yet.</p>
            @endforelse
        </div>
    </div>
    <div class="card-footer">
        {{$comments->links()}}
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit comment
        $('.edit-comment').on('click', function() {
            const commentId = $(this).data('id');
            $(`#comment-content-${commentId}`).addClass('d-none');
            $(`#comment-edit-${commentId}`).removeClass('d-none');
        });

        // Cancel edit
        $('.cancel-edit').on('click', function() {
            const commentId = $(this).data('id');
            $(`#comment-content-${commentId}`).removeClass('d-none');
            $(`#comment-edit-${commentId}`).addClass('d-none');
        });

        // Save comment
        $('.save-comment').on('click', function() {
            const commentId = $(this).data('id');
            const content = $(this).siblings('textarea').val();

            $.ajax({
                url: `{{ route('admin.community.comment.update', ':id') }}`.replace(':id', commentId),
                type: 'PUT',
                data: {
                    content: content,
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function () {
                    JDLoader.open();
                },
                success: function(response) {
                    JDLoader.close();
                    $(`#comment-content-${commentId} p`).text(content);
                    $(`#comment-content-${commentId}`).removeClass('d-none');
                    $(`#comment-edit-${commentId}`).addClass('d-none');
                    toastr.success('Comment updated successfully');
                },
                error: function(xhr) {
                    JDLoader.close();
                }
            });
        });

        // Delete comment
        $('.delete-comment').on('click', function() {
            const commentId = $(this).data('id');

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
                    $.ajax({
                        url: `{{ route('admin.community.comment.delete', ':id') }}`.replace(':id', commentId),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend: function () {
                            JDLoader.open();
                        },
                        success: function() {
                            JDLoader.close();
                            $(`#comment-${commentId}`).remove();
                            toastr.success('Comment deleted successfully');
                        },
                        error: function(xhr) {
                            JDLoader.close();
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
@push('styles')
<style>
    /* Comment Item */
    .comment-item {
        border: 1px solid #f0f0f0;
        padding: 12px 16px;
        border-radius: 15px;
    }

    .d-flex {
        display: flex;
        align-items: center;
    }

    .comment-item img {
        border: 2px solid #ddd;
        padding: 2px;
    }

    .comment-item h6 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .comment-item small {
        font-size: 12px;
        color: #999;
    }

    .comment-content {
        margin-top: 8px;
        font-size: 14px;
        color: #555;
    }

    .comment-content p {
        margin: 0;
        word-wrap: break-word;
    }

    .comment-edit-form {
        margin-top: 8px;
    }

    .comment-edit-form textarea {
        width: 100%;
        resize: none;
        border-radius: 4px;
        border: 1px solid #ddd;
        padding: 8px;
    }

    .comment-edit-form .btn {
        margin-right: 8px;
    }

    /* Dropdown Styling */
    .dropdown {
        position: relative;
    }

    .dropdown button {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        /* color: #333; */
    }

    .dropdown-menu {
        min-width: 120px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        overflow: hidden;
    }

    .dropdown-menu a {
        padding: 8px 16px;
        text-decoration: none;
        display: block;
        font-size: 14px;
        color: #333;
    }

    /* Button Styling */
    .btn {
        cursor: pointer;
    }

    .save-comment {
        background-color: #007bff;
        color: #fff;
        border: none;
    }

    .save-comment:hover {
        background-color: #0056b3;
    }

    .cancel-edit {
        background-color: #6c757d;
        color: #fff;
        border: none;
    }

    .cancel-edit:hover {
        background-color: #5a6268;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .comment-item img {
            width: 30px;
            height: 30px;
        }

        .comment-item h6 {
            font-size: 14px;
        }

        .comment-item small {
            font-size: 10px;
        }

        .comment-content {
            font-size: 12px;
        }

        .dropdown-menu {
            min-width: 100px;
        }
    }

</style>
@endpush
