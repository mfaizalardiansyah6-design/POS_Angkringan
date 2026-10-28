@if ($errors->any())
    <div class="form-errors" role="alert">
        <strong>Periksa kembali isian berikut</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
