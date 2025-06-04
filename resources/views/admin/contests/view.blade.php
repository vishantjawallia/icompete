@extends('admin.layouts.master')

@section('title', 'Contest details')

@section('content')
<div class="card mb-1">
    <div class="card-header">
        <h5>Manage Contest</h5>
        <div class="dropdown">

            {!!contestStatus($contest->status)!!}
            <button class="btn btn-primary border border-primary btn-sm" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i> options
            </button>
            <ul class="dropdown-menu">
                @if ($contest->status == 'pending')
                <li><a class="dropdown-item approve-btn" href="javascript:void(0)" data-message="Do you want to approve this contest?" data-url="{{route('admin.contest.approve', $contest->id)}}">Approve</a></li>
                <li><a class="dropdown-item reject-btn" href="javascript:void(0)" data-message="Do you want to reject this contest?" data-url="{{route('admin.contest.reject', $contest->id)}}">Reject</a></li>
                @endif
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-6 col-md-3">
        <div class="card">
            <a href="{{route('admin.contest.participants', $contest->id)}}" class="card-body d-flex pe-2 justify-content-between">
                <div class="mb-2">
                    <h6 class="text-muted small">Total Participants</h6>
                    <h5 class="mb-0 fw-bold">{{ $contest->participants->count() }}</h5>
                </div>
                <span class="dash-icon bg-primary my-auto">
                    <i class="far fa-file-alt"></i>
                </span>
            </a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card">
            <a href="{{route('admin.contest.submissions', $contest->id)}}" class="card-body d-flex pe-2 justify-content-between">
                <div class="mb-2">
                    <h6 class="text-muted small">Pending Submissions</h6>
                    <h5 class="mb-0 fw-bold">{{ $contest->submissions->count() }}</h5>
                </div>
                <span class="dash-icon bg-success my-auto">
                    <i class="fas fa-upload"></i>
                </span>
            </a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card">
            <a href="{{route('admin.contest.votes', $contest->id)}}" class="card-body d-flex pe-2 justify-content-between">
                <div class="mb-2">
                    <h6 class="text-muted small">Total Votes</h6>
                    <h5 class="mb-0 fw-bold">{{ $contest->votes->sum('quantity') }}</h5>
                </div>
                <span class="dash-icon bg-warning my-auto">
                    <i class="fas fa-vote-yea"></i>
                </span>
            </a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card">
            <a class="card-body d-flex pe-2 justify-content-between">
                <div class="mb-2">
                    <h6 class="text-muted small">Coins Earned</h6>
                    <h5 class="mb-0 fw-bold">{{ $contest->entry_coins + $contest->voting_coins }}</h5>
                </div>
                <span class="dash-icon bg-info my-auto">
                    <i class="fas fa-coins"></i>
                </span>
            </a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card">
            <a class="card-body d-flex pe-2 justify-content-between">
                <div class="mb-2">
                    <h6 class="text-muted small">Entry Coins</h6>
                    <h5 class="mb-0 fw-bold">{{ $contest->entry_coins }}</h5>
                </div>
                <span class="dash-icon bg-secondary my-auto">
                    <i class="fas fa-coins"></i>
                </span>
            </a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card">
            <a class="card-body d-flex pe-2 justify-content-between">
                <div class="mb-2">
                    <h6 class="text-muted small">Voting Coins</h6>
                    <h5 class="mb-0 fw-bold">{{ $contest->voting_coins }}</h5>
                </div>
                <span class="dash-icon bg-dark my-auto">
                    <i class="fas fa-coins"></i>
                </span>
            </a>
        </div>
    </div>
</div>
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Edit {{ $contest->title }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.contest.update', $contest->id) }}" class="row" enctype="multipart/form-data" method="post">
                @csrf
                <div class="col-md-6">
                    <!-- Title -->
                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ $contest->title }}" required>
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label for="category" class="form-label">Category</label>
                        <select name="category_id" id="category" class="form-select" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @if ($category->id == $contest->category_id) selected @endif>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required>{{ $contest->description }}</textarea>
                    </div>

                    <!-- Rules -->
                    <div class="form-group">
                        <label for="rules" class="form-label">Rules</label>
                        <textarea class="form-control" id="rules" name="rules" rows="3">{{ $contest->rules }}</textarea>
                    </div>
                    <div class="row">
                        <!-- Entry Type -->
                        <div class="form-group col-6">
                            <label for="entry_type" class="form-label">Entry Type</label>
                            <select class="form-select" id="entry_type" name="entry_type" required>
                                <option value="free" {{ $contest->entry_type == 'free' ? 'selected' : '' }}>Free</option>
                                <option value="paid" {{ $contest->entry_type == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="exclusive" {{ $contest->entry_type == 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                            </select>
                        </div>
                        <!-- Entry Fee -->
                        <div class="form-group col-6">
                            <label for="entry_fee" class="form-label">Entry Fee</label>
                            <input type="number" step="any" class="form-control" id="entry_fee" name="entry_fee" value="{{ $contest->entry_fee }}"
                                {{ $contest->entry_type == 'free' ? 'disabled' : '' }}>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="type">Contest Type</label>
                            <select name="type" class="form-select" required>
                                <option value="free" @if ($contest->type == 'free') selected @endif>Free</option>
                                <option value="paid" @if ($contest->type == 'paid') selected @endif>Paid</option>
                                <option value="exclusive" @if ($contest->type == 'exclusive') selected @endif>Exclusive</option>
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label for="contest_fee" class="form-label">Contest Fee</label>
                            <input type="number" step="any" class="form-control" id="contest_fee" name="amount" value="{{ $contest->amount }}"
                                {{ $contest->type == 'free' ? 'disabled' : '' }}>
                        </div>
                    </div>
                    <!-- Status -->
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="draft" {{ $contest->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending" {{ $contest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ $contest->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ $contest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="canceled" {{ $contest->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <!-- Prize -->
                        <div class="col-6 form-group">
                            <label for="prize" class="form-label">Winner Prize (%)</label>
                            <input type="number" name="prize" class="form-control" value="{{ $contest->prize }}" max="100" required>
                        </div>
                        <!-- Max Entries -->
                        <div class="col-6 form-group">
                            <label for="max_entries" class="form-label">Max Entries</label>
                            <input type="number" class="form-control" id="max_entries" name="max_entries" value="{{ $contest->max_entries }}">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Start Date -->
                        <div class="form-group col-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="text" class="form-control" id="start_date" name="start_date" value="{{ $contest->start_date }}" required>
                        </div>

                        <!-- End Date -->
                        <div class="form-group col-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="text" class="form-control" id="end_date" name="end_date" value="{{ $contest->end_date }}" required>
                        </div>
                    </div>

                    <!-- Voting Dates -->
                    <div class="row">
                        <div class="form-group col-6">
                            <label for="voting_start_date" class="form-label">Voting Start Date</label>
                            <input type="text" class="form-control" id="voting_start_date" name="voting_start_date" value="{{ $contest->voting_start_date }}" required>
                        </div>
                        <div class="form-group col-6">
                            <label for="voting_end_date" class="form-label">Voting End Date</label>
                            <input type="text" class="form-control" id="voting_end_date" name="voting_end_date" value="{{ $contest->voting_end_date }}" required>
                        </div>
                    </div>
                    <!-- Dynamic Requirements -->
                    <div class="form-group">
                        <label for="requirements" class="form-label">Requirements</label>
                        <div id="requirements-container">
                            @foreach ($contest->requirements as $index => $requirement)
                                <div class="requirement-item mb-2" data-index="{{ $index }}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="mb-0 text-sm" for="">Name</label>
                                            <input type="text" class="form-control" name="requirements[{{ $index }}][name]" value="{{ $requirement['name'] }}"
                                                placeholder="Name" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="mb-0 text-sm" for="">Type</label>
                                            <select class="form-select" name="requirements[{{ $index }}][type]" required>
                                                <option value="text" {{ $requirement['type'] == 'text' ? 'selected' : '' }}>Text</option>
                                                <option value="image" {{ $requirement['type'] == 'image' ? 'selected' : '' }}>Image</option>
                                                <option value="video" {{ $requirement['type'] == 'video' ? 'selected' : '' }}>Video</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="mb-0 text-sm" for="">Desc</label>
                                            <input type="text" class="form-control" name="requirements[{{ $index }}][description]"
                                                value="{{ $requirement['description'] }}" placeholder="Description">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-requirement"><i class="fa fa-trash"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-primary mt-2 btn-sm" id="add-requirement"><i class="fa fa-plus"></i> Add Requirement</button>
                        </div>

                    </div>

                    <!-- Featured -->
                    <div class="form-check form-group">
                        <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" {{ $contest->featured ? 'checked' : '' }}>
                        <label class="form-check-label" for="featured">Featured Contest</label>
                    </div>

                    <!-- Image -->
                    <div class="form-group">
                        <label for="image" class="form-label">Contest Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        @if ($contest->image)
                            <img src="{{ my_asset($contest->image) }}" alt="Contest Image" class="img-fluid mt-2" style="max-height: 200px;">
                        @endif
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Contest</button>
            </form>
        </div>

    </div>

@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let requirementIndex = {{ count($contest->requirements) }};

            document.getElementById('add-requirement').addEventListener('click', function() {
                const container = document.getElementById('requirements-container');
                const newRequirement = document.createElement('div');
                newRequirement.classList.add('requirement-item', 'mb-2');
                newRequirement.setAttribute('data-index', requirementIndex);
                newRequirement.innerHTML = `
                    <div class="row">
                        <div class="col-md-4">
                            <label class="mb-0 text-sm" for="">Name</label>
                            <input type="text" class="form-control" name="requirements[${requirementIndex}][name]" placeholder="Name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-0 text-sm" for="">Type</label>
                            <select class="form-select" name="requirements[${requirementIndex}][type]" required>
                                <option value="text">Text</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-0 text-sm" for="">Desc</label>
                            <input type="text" class="form-control" name="requirements[${requirementIndex}][description]" placeholder="Description" required>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm mt-2 remove-requirement"><i class="fa fa-trash"></i></button>
                `;
                container.appendChild(newRequirement);
                requirementIndex++;
            });

            document.getElementById('requirements-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-requirement')) {
                    e.target.closest('.requirement-item').remove();
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#start_date", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                disableMobile: true,
                onChange: function(selectedDates, dateStr) {
                    console.log(selectedDates)
                    flatpickr("#end_date", {
                        enableTime: true,
                        dateFormat: "Y-m-d H:i",
                        disableMobile: true,
                        minDate: selectedDates[0],
                    });
                },
            });

            flatpickr("#end_date", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                disableMobile: true,
                minDate: "{{$contest->start_date}}",
            });

            flatpickr("#voting_start_date", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                disableMobile: true,
                onChange: function(selectedDates, dateStr) {
                    flatpickr("#voting_end_date", {
                        enableTime: true,
                        dateFormat: "Y-m-d H:i",
                        disableMobile: true,
                        minDate: selectedDates[0],
                    });
                },
            });
            flatpickr("#voting_end_date", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                disableMobile: true,
                minDate:  "{{$contest->voting_start_date}}"
            });
        });

    </script>
@endpush

@push('styles')
<link href="{{ static_asset('admin/vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
