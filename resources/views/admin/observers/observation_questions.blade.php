@extends('admin.layouts.layout')

@section('content')
<div class="wrapper">
    @include('admin.layouts.sidebar')

    <div class="main">
        @include('admin.layouts.navbar')

        <main class="content">
            <div class="container-fluid p-0">
                <div class="flex" style="justify-content: space-between; align-items: center;">
                    <h1 class="text-lg font-semibold text-[#667085]" style="font-size:24px; display:flex; gap:10px; align-items:center;justify-content:space-between;">
                        <div>
                            Observation Questions and Headers
                        </div>
                        <button class="btn btn-primary mb-3" onclick="openAddHeaderModal()">Add Header</button>
                    </h1>
                </div>

                @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="overflow-x-auto">
                    <div class="mb-4 flex" style="gap:10px; padding:10px">
                        @if (isset($data))
                        <div class="questions mb-3">
                            @foreach ($data as $header)
                            <div class="flex justify-between items-center mb-4">
                                <h1 class="text-lg font-semibold text-[#667085]" style="font-size:24px; display:flex; gap:10px;justify-content:space-between ;align-items:center;">
                                    <div>
                                        {{ $header['name'] }}
                                    </div>
                                    <div class="">
                                        <button class="btn btn-primary" onclick="openEditHeaderModal('{{ $header['header_id'] }}')">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <!-- Add Question Button -->
                                        <button class="btn btn-primary" onclick="openAddQuestionModal('{{ $header['header_id'] }}')">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                        <!-- Delete Header Button -->
                                        <form action="{{ route('headers.deleteHeader', $header['header_id']) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>

                                </h1>
                            </div>

                            <!-- Table for questions -->
                            @if (!empty($header['questions']))
                            <table class="w-full border border-gray-300 text-left mb-4" style="width: 100%;">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="col-9 border px-4 py-2">Question</th>
                                        <th class="col-1 border px-4 py-2">Max Rating</th>
                                        <th class="col-2 border px-4 py-2 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($header['questions'] as $question)
                                    <tr>
                                        <td class="border px-4 py-2">{{ $question['name'] }}</td>
                                        <td class="border px-4 py-2">{{ $question['max_rating'] }}</td>
                                        <td class="border px-4 py-2">
                                            <div class="flex" style="display:flex;justify-content:center; gap: 5px;">
                                                <button class="btn btn-primary" onclick="openEditQuestionModal('{{ $question['question_id'] }}')">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <!-- Delete Question Button -->
                                                <form action="{{ route('questions.destroy', $question['question_id']) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p>No questions found for this header</p>
                            @endif
                            @endforeach
                        </div>
                        @else
                        <p>No Observation Questions Found</p>
                        @endif
                    </div>
                </div>
            </div>

        </main>

    </div>
</div>

<!-- Add Header Modal -->
<div id="addHeaderModal" class="modal" style="display: none;">
    <div class="modal-content" style="text-align: left;">
        <span class="close" onclick="closeAddHeaderModal()">&times;</span>
        <h2>Add Header</h2>
        <form action="{{ route('headers.storeHeader') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label for="headerName">Header Name</label>
                <input type="text" name="name" id="headerName" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Header</button>
        </form>
    </div>
</div>

<!-- Add Question Modal -->
<div id="addQuestionModal" class="modal" style="display: none;">
    <div class="modal-content" style="text-align: left;">
        <span class="close" onclick="closeAddQuestionModal()">&times;</span>
        <h2>Add Question</h2>
        <form action="{{ route('questions.storeQuestion') }}" method="POST">
            @csrf
            <input type="hidden" name="header_id" id="headerId">
            <div class="form-group mb-3">
                <label for="questionName">Question Name</label>
                <input type="text" name="name" id="questionName" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="maxRating">Max Rating</label>
                <input type="number" name="max_rating" id="maxRating" max="10" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Question</button>
        </form>
    </div>
</div>
<!-- Edit Header Modal -->
<div id="EditHeaderModal" class="modal" style="display: none;">
    <div class="modal-content" style="text-align: left;">
        <span class="close" onclick="closeEditHeaderModal()">&times;</span>
        <h2>Edit Header</h2>
        <form action="{{ route('headers.editHeader') }}" method="POST">
            @csrf
            <input type="hidden" name="header_id" id="headerEditId">
            <div class="form-group mb-3">
                <label for="questionName">Header Name</label>
                <input type="text" name="header_name" id="headername" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Edit Header</button>
        </form>
    </div>
</div>
<!-- Edit Question Modal -->
<div id="EditQuestionModal" class="modal" style="display: none;">
    <div class="modal-content" style="text-align: left;">
        <span class="close" onclick="closeEditQuestionModal()">&times;</span>
        <h2>Edit Question</h2>
        <form action="{{ route('questions.editQuestion') }}" method="POST">
            @csrf
            <input type="hidden" name="question_id" id="questionEditId">
            <div class="form-group mb-3">
                <label for="questionName">Question</label>
                <input type="text" name="question_name" id="questionname" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="maxRating">Max Rating</label>
                <input type="number" name="max_rating" id="maxRating" class="form-control" max="10" required>
            </div>

            <button type="submit" class="btn btn-primary">Edit Question</button>
        </form>
    </div>
</div>

<style>
    /* Modal Background */
    .modal {
        display: none;
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        /* Shadow effect */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Modal Content */
    .modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        width: 90%;
        text-align: center;
    }

    .modal-content .close {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
    }
</style>
@endsection

@section('page_js')
<script>
    function openAddHeaderModal() {
        document.getElementById('addHeaderModal').style.display = 'flex';
    }

    function closeAddHeaderModal() {
        document.getElementById('addHeaderModal').style.display = 'none';
    }

    function openAddQuestionModal(headerId) {
        document.getElementById('headerId').value = headerId;
        document.getElementById('addQuestionModal').style.display = 'flex';
    }

    function closeAddQuestionModal() {
        document.getElementById('addQuestionModal').style.display = 'none';
    }

    function openEditHeaderModal(headerId) {
        // console.log(headerId);
        document.getElementById('headerEditId').value = headerId;
        document.getElementById('EditHeaderModal').style.display = 'flex';
    }

    function closeEditHeaderModal() {
        document.getElementById('EditHeaderModal').style.display = 'none';
    }

    function openEditQuestionModal(questionId) {
        document.getElementById('questionEditId').value = questionId;
        document.getElementById('EditQuestionModal').style.display = 'flex';
    }

    function closeEditQuestionModal() {
        document.getElementById('EditQuestionModal').style.display = 'none';
    }

    // Close modals when clicking outside of them
    window.onclick = function(event) {
        const modals = ['addHeaderModal', 'addQuestionModal', 'EditHeaderModal', 'EditQuestionModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    };
</script>
@endsection