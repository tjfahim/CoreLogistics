@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Agent Manage</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('agents.index') }}">Agents</a></li>
                    <li class="active">Add Agent</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    <div class="col-lg-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between ">
                <strong>Add New Agent</strong>
                <a href="{{ route('agents.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">

                @cannot('add-agents')
                    <div class="alert alert-danger">
                        You do not have permission to add agents.
                    </div>
                @endcannot

                @can('add-agents')
                    <form action="{{ route('agents.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="font-weight-bold">Agent Name</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Enter name">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Agent Type</label>
                            <select name="type" class="form-control @error('type') is-invalid @enderror">
                                <option value="">-- Select Type --</option>
                                <option value="freight_forwarder" {{ old('type')=='freight_forwarder' ? 'selected' : '' }}>
                                    Freight Forwarder
                                </option>
                                <option value="cnf_agent" {{ old('type')=='cnf_agent' ? 'selected' : '' }}>
                                    CNF Agent
                                </option>
                            </select>
                            @error('type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Company Name</label>
                            <input type="text"
                                   name="company_name"
                                   value="{{ old('company_name') }}"
                                   class="form-control @error('company_name') is-invalid @enderror"
                                   placeholder="Enter company name">
                            @error('company_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Email</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="example@mail.com">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Phone</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="01xxxxxxxxx">
                            @error('phone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Address</label>
                            <textarea name="address" rows="3"
                                      class="form-control @error('address') is-invalid @enderror"
                                      placeholder="Enter full address">{{ old('address') }}</textarea>
                            @error('address')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Notes</label>
                            <textarea name="notes" rows="3"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      placeholder="Write additional notes">{{ old('notes') }}</textarea>
                            @error('notes')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary btn-sm px-3">
                            <i class="fa fa-save"></i> Save Agent
                        </button>
                    </form>
                @endcan

            </div>

        </div>

    </div>

</div>

@endsection