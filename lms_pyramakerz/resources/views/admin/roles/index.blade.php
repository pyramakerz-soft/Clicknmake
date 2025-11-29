@extends('admin.layouts.layout')
@section('page_css')
    <style>

    </style>
@endsection
@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <div class="py-12 w-full">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2">
                        <div class="flex justify-end p-2">
                            @can('create role')
                                <a href="{{ route('admin.roles.create') }}" class="btn btn-success">Create Role</a>
                            @endcan
                        </div>
                        <div class="flex flex-col">
                            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                        <table class="table table-responsive">
                                            <thead class="">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Name</th>
                                                    <th scope="col" class="relative px-6 py-3">
                                                        <span class="sr-only">Edit</span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="">
                                                @foreach ($roles as $role)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="d-flex items-center">
                                                                {{ $role->name }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex justify-content-end">
                                                                <div class="d-flex align-items-center">
                                                                    @can('update role')
                                                                        <a href="{{ route('admin.roles.edit', $role->id) }}"
                                                                            class="btn btn-primary">Edit</a>
                                                                    @endcan
                                                                    @can('delete role')
                                                                        <form
                                                                            class="px-4 py-2 bg-red-500 hover:bg-red-700 text-white rounded-md"
                                                                            method="POST"
                                                                            action="{{ route('admin.roles.destroy', $role->id) }}"
                                                                            onsubmit="return confirm('Are you sure?');">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit"
                                                                                class="btn btn-danger">Delete</button>
                                                                        </form>
                                                                    @endcan
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
