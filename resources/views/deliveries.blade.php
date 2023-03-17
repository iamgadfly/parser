<link rel="stylesheet" href="{{ asset('css/course.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pretty-checkbox@3.0/dist/pretty-checkbox.min.css" />

<div class="container" style="
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
">
    <div class="container__block" style="
    width: 700px;
">
<form action="/save_deliveries" method="POST" style="700px">
    @csrf
@foreach($deliveries as $delivery)
    <div class="block__container">
        <div class="block__container-text" style="    display: flex;
    justify-content: space-around;
    padding: 25px;
    align-items: center;">
            <div class="name">Название {{$delivery->name}}</div>
            <div class="name">Вес: {{$delivery->weight}} кг</div>
{{--            <input type="hidden" name="{{$delivery->id}}" value="{{$delivery->name}}">--}}
            <input class="check nput-style" placeholder="{{$delivery->price}}" name="price_{{$delivery->id}}" style="width: 250px;">
{{--            <input type="hidden" name="wight" value="{{$delivery->weight}}">--}}
        </div>
    </div>

@endforeach
    <div class="button__block" style="display: flex; justify-content: center">
        <button type="submit">Сохранить</button>
    </div>
</form>
    </div>
</div>

