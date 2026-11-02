@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Shows and Auctions</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <select id="monthInput">
                    <option value="-1">Show All</option>
                    <option value="0">Monthly</option>
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div id="eventGrid" class="eventGrid">
                    <div class="row eventGridHeading">
                        <div class="col-4">Date</div>
                        <div class="col-4">Description</div>
                        <div class="col-4">Location</div>
                    </div>
                    <div class="row eventGridHeading">
                        <div class="col-12" colspan="3">Details / Directions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
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
            let counter = 0;
            let thisClass = '';
            $(xml).find("show").each(function (index) {
                let description = $(this).find("description").text();
                let id = $(this).attr("id");
                if (description.trim() !== "") {
                    let month = $(this).find("month").text();
                    let formattedMonth = moment(month, 'M').format('MMMM');

                    // Main row - first of two rows for this show
                    let mainRow = "<div class='row'>";
                    mainRow += "    <div class='col-4'>" + formattedMonth + ' ' + $(this).find("date").text() + "</div>";
                    mainRow += "    <div class='col-4'>" + description + "</div>";
                    mainRow += "    <div class='col-4'>" + $(this).find("location").text() + "</div>";
                    mainRow += "</div>";

                    // Details row - second of two rows for this show
                    let detailsRow = "<div class='row'>";
                    detailsRow += "    <div class='col-12'>" + $(this).find("detail").text();
                    detailsRow += "    <br /><a href='directions?id=" + id + "' class='direction'>Click for directions</a>";
                    detailsRow += "     </div>";
                    detailsRow += " </div>";

                    let thisRow = "<div class='row eventGridRow' data-index='" + month + "'>" + mainRow + detailsRow + "</div>";

                    $("#eventGrid").append(thisRow);
                }
            });
            $(".eventGridRow:even").addClass("alt");
        }

        // Set up month filtering - this is a simplified version
        $('#monthInput').change(function() {
            let selectedMonth = parseInt($(this).val());
            $(".eventGridRow").removeClass("alt");

            if (selectedMonth === -1) {
                $(".eventGridRow").show();
            } else {
                $(".eventGridRow").each(function() {
                    let rowMonth = parseInt($(this).data("index"));
                    if (rowMonth === selectedMonth) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
            $(".eventGridRow:even").addClass("alt");
        });
    });
    </script>
@endsection
