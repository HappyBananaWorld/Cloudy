<h1>لیست فایل‌ها</h1>
<ul>
    @foreach($files as $file)
        <a href="{{asset("uploads/$file")}}">
        <li>{{ $file }}</li>
        </a>
    @endforeach
</ul>
