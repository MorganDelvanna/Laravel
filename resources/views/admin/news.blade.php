@extends('layouts.app')

@section('content')
    <div class='container'>
        <div class="row">
            <div class="col-12">
                <h1>News Items</h1>
            </div>
        </div>
        <div class="row card hidden" id="newsForm">
            <div class="col-12">                
                <form  method='post' action="{{ route('news.saveNews') }}" onsubmit="return validate(this);">
                    @csrf
                    <div style="margin-top: 5px;">
                        <label class='form-label' for="publish_date">Publish Date: </label><input class='form-control' type="text" id="publish_date" name="publish_date" value="" /><br />
                        <label class='form-label' for="header">Header: </label><input class='form-control' type="text" id="header" name="header" value="" /><br />
                        <label class='form-label' for="description">Description: </label><input class='form-control' style="min-width:500px;" type="text" id="description" name="description" value="" /><br />
                        <label class='form-label' for="archived">Archived: </label><input class='form-check-input' type="checkbox" id="archived" name="archived" value="1" /><br />
                    </div>
                    <div>
                        <input type='submit' class='btn btn-primary' value='Save'>
                        <input type='button' class='btn btn-secondary' value='Cancel' onclick="$('#newsForm').toggleClass('hidden');">
                    </div>
                    <input type="hidden" id="newsID" name="newsID" value=""/>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <button type="button" class="btn btn-primary" onclick="$('#newsForm').toggleClass('hidden');">Add New Item</button>
            </div>
        </div>
        <div class="row  align-items-center">
            <div class="col-12">
                <table class="pfgaTable">
                    <thead>
                        <tr>
                            <th>Publish Date</th>
                            <th>Header</th>
                            <th>Description</th>
                            <th>Archived</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($newsItems as $item)
                            <tr>
                                <td>{{ $item->publish_date }}</td>
                                <td>{{ $item->header }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->archived ? 'Yes' : 'No' }}</td>
                                <td>
                                        <button type="button" class="btn btn-primary" onclick='editItem({{ $item->id }}, @json($item->publish_date?->format('Y-m-d')), @json($item->header), @json($item->description), @json($item->archived))'>Edit</button>                                    
                                    <form method="POST" action="{{ route('news.archiveNews', ['id' => $item->id]) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">
                                        @if(!$item->archived)
                                            Archive
                                        @else
                                            Unarchive
                                        @endif
                                        </button>
                                    </form>                                    
                                    <form method="POST" action="{{ route('news.deleteNews', ['id' => $item->id]) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No calendar events found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>            
        </div>
    </div>
    <link rel="stylesheet" type="text/css" href="/js/dateTimePicker/jquery.datetimepicker.min.css" />
    <script src="/js/dateTimePicker/jquery.datetimepicker.full.min.js"></script>
    <script type="text/javascript">
        function editItem(id, publish_date, header, description, archived) {            
            $('#newsID').val(id);
            $('#publish_date').val(publish_date || '');
            $('#header').val(header || '');
            $('#description').val(description || '');
            $('#archived').prop('checked', archived || false);
            $('#newsForm').toggleClass('hidden');
        }

        $(document).ready(function () {
            $('#publish_date').datetimepicker({
                format: 'Y-m-d',
                step: 15
            });
        });
    </script>
@endsection