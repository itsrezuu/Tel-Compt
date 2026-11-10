@if ($errors->any())
    <div class="flash error" role="alert">
        <ul class="bm-errors">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
