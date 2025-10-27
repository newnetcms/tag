@php($name = $name ?? 'tags')
@php($label = $label ?? 'Tags')

<div class="form-group row component-{{ $name }}">
    <label for="{{ $name }}" class="col-12 col-form-label font-weight-600">{{ $label }}</label>
    <div class="col-12">
        <input type="hidden" name="{{ $name }}">
        <select name="{{ $name }}[]"
                id="{{ $name }}"
                multiple
                class="form-control tags x @if (!empty($remote)) remote @endif @error(get_dot_array_form($name)) is-invalid @enderror"
                @if (!empty($remote_url))
                    data-remote-url="{{ $remote_url }}"
                @endif
                @if (!empty($remote_args))
                    data-remote-args="{{ $remote_args }}"
                @endif
                placeholder="{{ $placeholder ?? $label }}"
        >
            @foreach(object_get($item, $name) ?? [] as $tag)
                <option value="{{ $tag->id ?? $tag }}" selected>
                    {{ $tag->name ?? $tag }}
                </option>
            @endforeach
            @foreach(get_another_tag(object_get($item, $name)->pluck('id')->toArray()) as $tag)
                    <option value="{{ $tag->id ?? $tag }}">
                        {{ $tag->name ?? $tag }}
                    </option>
            @endforeach
        </select>

        @error(get_dot_array_form($name))
            <span class="invalid-feedback text-left">
                <strong>{{ $message }}</strong>
            </span>
        @enderror

        @if(!empty($helper))
            <span class="helper-block">
                {!! $helper !!}
            </span>
        @endif
    </div>
</div>

@assetadd('select2', asset("vendor/newnet-admin/plugins/select2/dist/css/select2.min.css"))
@assetadd('select2', asset("vendor/newnet-admin/plugins/select2/dist/js/select2.min.js"), ['jquery'])
@assetadd('select2-bootstrap4', asset("vendor/newnet-admin/plugins/select2-bootstrap4/dist/select2-bootstrap4.min.css"), ['jquery', 'bootstrap', 'select2'])
@assetadd('tag-script', "vendor/tag/admin/js/tag.js", ['jquery', 'select2'])
