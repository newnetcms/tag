@extends('core::admin.master')

@section('meta_title', __('tag::tag.index.page_title'))

@section('page_title', __('tag::tag.index.page_title'))

@section('page_subtitle', __('tag::tag.index.page_subtitle'))

@section('breadcrumb')
    <nav aria-label="breadcrumb" class="col-sm-4 order-sm-last mb-3 mb-sm-0 p-0 ">
        <ol class="breadcrumb d-inline-flex font-weight-600 fs-13 bg-white mb-0 float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ trans('dashboard::message.index.breadcrumb') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('tag::tag.index.breadcrumb') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fs-17 font-weight-600 mb-0">
                        {{ __('tag::tag.index.page_title') }}
                    </h6>
                </div>
                <div class="text-right">
                    <div class="actions">
                        @admincan('tag.admin.tag.create')
                            <a href="{{ route('tag.admin.tag.create') }}" class="action-item">
                                <i class="fa fa-plus"></i>
                                {{ __('core::button.add') }}
                            </a>
                        @endadmincan
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form class="form-inline newnet-table-search">
                @input(['item' => null, 'name' => 'name', 'label' => __('tag::tag.name'), 'value' => request('name')])

                <button type="submit" class="btn btn-primary mr-1">
                    {{ __('core::button.search') }}
                </button>
                <a href="{{ route('tag.admin.tag.index') }}" class="btn btn-danger">
                    {{ __('core::button.cancel') }}
                </a>
            </form>

            <table class="table table-striped table-bordered dt-responsive nowrap bootstrap4-styling">
                <thead>
                <tr>
                    <th>{{ __('#') }}</th>
                    <th>{{ __('tag::tag.name') }}</th>
                    <th>{{ __('tag::tag.created_at') }}</th>
                    {{--<th>@translatableHeader</th>--}}
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>
                            <a href="{{ route('tag.admin.tag.edit', $item->id) }}">
                                {{ $item->name }}
                            </a>
                            <a href="{{ $item->url }}" target="_blank" title="{{ __('core::button.view') }}">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </td>
                        <td>{{ $item->created_at }}</td>
                        {{--<td>
                            @translatableStatus(['editUrl' => route('tag.admin.tag.edit', $item->id)])
                        </td>--}}
                        <td class="text-right">
                            @admincan('tag.admin.tag.edit')
                                <a href="{{ route('tag.admin.tag.edit', $item->id) }}" class="btn btn-success-soft btn-sm mr-1">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                            @endadmincan

                            @admincan('tag.admin.tag.destroy')
                                <table-button-delete url-delete="{{ route('tag.admin.tag.destroy', $item->id) }}"></table-button-delete>
                            @endadmincan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {!! $items->appends(Request::all())->render() !!}
        </div>
    </div>
@stop
