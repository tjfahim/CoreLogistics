@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4"><div class="page-header float-left"><div class="page-title"><h1>Edit Office Bill</h1></div></div></div>
    <div class="col-sm-8"><div class="page-header float-right"><div class="page-title">
        <ol class="breadcrumb text-right">
            <li><a href="{{ route('office-bills.index') }}">Office Bills</a></li>
            <li class="active">Edit</li>
        </ol>
    </div></div></div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Edit Office Bill</strong>
                <a href="{{ route('office-bills.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('office-bills.update', $bill->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label class="font-weight-bold">Office</label>
                        <select name="office_id" class="form-control @error('office_id') is-invalid @enderror">
                            @foreach($offices as $o)
                                <option value="{{ $o->id }}" {{ old('office_id', $bill->office_id)==$o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                            @endforeach
                        </select>
                        @error('office_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Year</label>
                            <input type="number" name="year" min="2000" max="2100"
                                   value="{{ old('year', $bill->year) }}"
                                   class="form-control @error('year') is-invalid @enderror">
                            @error('year')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Month</label>
                            <select name="month" class="form-control @error('month') is-invalid @enderror">
                                @for($m=1; $m<=12; $m++)
                                    <option value="{{ $m }}" {{ old('month', $bill->month)==$m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0,0,0,$m,1)) }}
                                    </option>
                                @endfor
                            </select>
                            @error('month')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Amount (BDT)</label>
                        <input type="number" step="0.01" min="0" name="amount"
                               value="{{ old('amount', $bill->amount) }}"
                               class="form-control @error('amount') is-invalid @enderror">
                        @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Note</label>
                        <input type="text" name="note" value="{{ old('note', $bill->note) }}" class="form-control">
                    </div>
                    <button class="btn btn-primary btn-sm px-3"><i class="fa fa-save"></i> Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection