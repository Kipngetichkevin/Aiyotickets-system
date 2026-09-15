@vite('resources/css/app.css')

@include('backend.includes.Header')
@include('backend.includes.Sidebar')

<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <div class="w-full lg:ps-64">
        <h2 class="text-2xl font-bold"> Budgets </h2>

<!-- resources/views/backend/budgets/index.blade.php -->

<div class="container mt-4">
    <h2 class="mb-4">Budget List</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBudgetModal">Add Budget</button>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Cost</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgets as $budget)
            <tr>
                <td>{{ $budget->name }}</td>
                <td>{{ ucfirst($budget->category) }}</td>
                <td>{{ $budget->cost }}</td>
                <td>
                    <a href="{{ route('backend.budgets.edit', $budget->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <a href="{{ route('backend.budgets.show', $budget->id) }}" class="btn btn-sm btn-info">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add Budget Modal -->
<div class="modal fade" id="addBudgetModal" tabindex="-1" aria-labelledby="addBudgetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBudgetModalLabel">Add Budget</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('backend.budgets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_id" class="form-label">Event</label>
                        <select class="form-control" id="event_id" name="event_id" required>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category" required>
                            <option value="personnel">Personnel</option>
                            <option value="supplier">Supplier</option>
                            <option value="debtor">Debtor</option>
                            <option value="creditor">Creditor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cost" class="form-label">Cost</label>
                        <input type="number" class="form-control" id="cost" name="cost">
                    </div>
                    <div class="mb-3">
                        <label for="contract_signature" class="form-label">Contract Signature</label>
                        <input type="file" class="form-control" id="contract_signature" name="contract_signature">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    </div>
</div>


<!-- Required plugins -->
<script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>

  