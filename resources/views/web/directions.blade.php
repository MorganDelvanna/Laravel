@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <h1>Directions to Gun Shows and Auctions</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <p id="Directions">
            </p>
            <p id="Map" align="center">
            </p>
        </div>
    </div>

    <script src="js/bootstrap.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $.ajax({
                type: "GET",
                url: "{{ asset('shows.xml') }}",
                dataType: "xml",
                success: function(xml) {
                    parseXml(xml);
                },
                error: function() {
                    console.log("Error loading XML file");
                }
            });

            function parseXml(xml) {
                var showId = querySt('id');


                $(xml).find("show[id='" + showId + "']").each(function () {

                    $("#Directions").append($(this).find("directions").text());
                    $("#Map").append($(this).find("map").text());

                });
            }

            function querySt(param) {
                var qstring = window.location.search.substring(1);

                var paramList = qstring.split("&");

                for (i = 0; i < paramList.length; i++) {
                    val = paramList[i].split("=");

                    if (val[0] == param) {
                        return val[1];
                    }
                }
            }


        });

    </script>

@endsection
