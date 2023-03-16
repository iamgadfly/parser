<link rel="stylesheet" href="{{ asset('css/course.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pretty-checkbox@3.0/dist/pretty-checkbox.min.css" />


<div style="
display: flex;
justify-content: center;
align-items: center;
height: 100%;
">
    <div class="container mt-5">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-md-8">
                <form method="POST" action="/chnage_course/{{$course->name}}" id="regForm" style="width: 500px;">
                    @csrf
                    <h1 id="register">Обновлние курса</h1>

                    <div class="tab">
                        <p>
                            <input class="check nput-style" placeholder="{{ $course->price }}" name="price">
                        </p>
                        {{--  <p>Обновление курса автоматически</p>  --}}

                        <div style="margin-top: 10px" class="pretty p-default p-curve p-toggle">
                            <input type="checkbox" name="is_auto" />
                            <div class="state p-success p-on">
                                <label>Автоматическое обновлние</label>
                            </div>
                            <div class="state p-danger p-off">
                                <label>Обновление в ручном режиме</label>
                            </div>
                        </div>
                    </div>

                    <div style="overflow:auto;" id="nextprevious">
                        <div style="float:right;">

                            <button
                                style="    background: #6A1B9A;
                            border-radius: 10%;
                        }"
                                type="submit" id="nextBtn" onclick="nextPrev(1)"><i
                                    class="fa-angle-double-right font">Сохранить</i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
    var currentTab = 0;
    document.addEventListener("DOMContentLoaded", function(event) {


        showTab(currentTab);

    });

    function showTab(n) {
        var x = document.getElementsByClassName("tab");
        x[n].style.display = "block";
        if (n == 0) {
            document.getElementById("prevBtn").style.display = "none";
        } else {
            document.getElementById("prevBtn").style.display = "inline";
        }
        if (n == (x.length - 1)) {
            document.getElementById("nextBtn").innerHTML = '<i class="fa fa-angle-double-right"></i>';
        } else {
            document.getElementById("nextBtn").innerHTML = '<i class="fa fa-angle-double-right"></i>';
        }
        fixStepIndicator(n)
    }

    function nextPrev(n) {
        var x = document.getElementsByClassName("tab");
        if (n == 1 && !validateForm()) return false;
        x[currentTab].style.display = "none";
        currentTab = currentTab + n;
        if (currentTab >= x.length) {

            document.getElementById("nextprevious").style.display = "none";
            document.getElementById("all-steps").style.display = "none";
            document.getElementById("register").style.display = "none";
            document.getElementById("text-message").style.display = "block";




        }
        showTab(currentTab);
    }

    function validateForm() {
        var x, y, i, valid = true;
        x = document.getElementsByClassName("tab");
        y = x[currentTab].getElementsByTagName("input");
        for (i = 0; i < y.length; i++) {
            if (y[i].value == "") {
                y[i].className += " invalid";
                valid = false;
            }


        }
        if (valid) {
            document.getElementsByClassName("step")[currentTab].className += " finish";
        }
        return valid;
    }


    function fixStepIndicator(n) {
        var i, x = document.getElementsByClassName("step");
        for (i = 0; i < x.length; i++) {
            x[i].className = x[i].className.replace(" active", "");
        }
        x[n].className += " active";
    }


    $('input').on('keydown', function(e) {
        if (e.key.length == 1 && e.key.match(/[^0-9'".]/)) {
            return false;
        };
    })
</script>
