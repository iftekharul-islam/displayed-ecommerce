$(function() {

    "use strict";

    $(document).ready(function () {
        $('#country-dropdown').on('change', function () {
            var country_id = this.value;
            $("#District-dropdown").html('');
            $.ajax({
                url: $('#url').val() + '/admin/get-dictrict-by-country',
                type: "POST",
                data: {
                    country_id: country_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (result) {
                    $.each(result.district, function (key, value) {
                        $("#district-dropdown").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    $('#area-dropdown').html('<option value="">Select District First</option>');
                }
            });


        });
        $('#district-dropdown').on('change', function () {
            var district_id = this.value;
            $("#area-dropdown").html('');
            $.ajax({
                url: $('#url').val() + '/admin/get-areas-by-district',
                type: "POST",
                data: {
                    division_id: divisions_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (result) {
                    $.each(result.areas, function (key, value) {
                        $("#areas-dropdown").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });


        });
    });

});
