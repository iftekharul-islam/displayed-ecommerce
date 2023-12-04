$(function () {
    'use strict';

    $(document).ready(function () {
        let btns = $('.btn-press');
        $.each(btns, function (index, btn) {

           if (index > 0)
           {
               $(this).addClass('d-none');
           }
        });
        $(document).on('click', '.ai_writer', function (e) {
            let selector = $(this);
            selector.find('.a_writer_text').addClass('d-none');
            selector.find('.a_writer_loader').removeClass('d-none');
            let name = selector.data('name');
            let length = selector.data('length');
            let topic = selector.data('topic');
            let keyword = $('.' + topic).val();
            if (!keyword)
            {
                alert('Please Enter title/name first');
                return false;
            }
            let token = $('meta[name="csrf-token"]').attr('content');
            let url = selector.data('url');
            let extra_description = selector.data('extra_query');
            let data = {
                prompt: keyword,
                _token: token,
                length: length,
                long_description: extra_description
            };
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (response) {
                    selector.find('.a_writer_text').removeClass('d-none');
                    selector.find('.a_writer_loader').addClass('d-none');
                    let field = $('.'+name);

                    if (extra_description)
                    {
                        field.summernote("code", response.content);
                    }
                    else{
                        field.val(response.content);
                    }
                },
                error: function (error) {
                    selector.find('.a_writer_text').removeClass('d-none');
                    selector.find('.a_writer_loader').addClass('d-none');
                    alert('Something went wrong, please try again later');
                },
                fail: function (error) {
                    selector.find('.a_writer_text').removeClass('d-none');
                    selector.find('.a_writer_loader').addClass('d-none');
                    alert('Something went wrong, please try again later');
                }
            });
        });

        $(document).on('click', '.generate_content_for_me', function (e) {
            e.preventDefault();
            let selector = $(this);
            let loader_selector = $('.btn_loader');
            selector.addClass('d-none');
            loader_selector.removeClass('d-none');
            let use_case = $('#use_case').val();
            let primary_keyword = $('#primary_keyword').val();
            let variants = $('#variants').val();

            if (!use_case || !primary_keyword || !variants)
            {
                alert('Please Select All the necessary fields');
                selector.removeClass('d-none');
                loader_selector.addClass('d-none');
                return false;
            }
            let token = $('meta[name="csrf-token"]').attr('content');
            let url = selector.data('url');

            let data = {
                prompt: 'Write '+variants+' '+use_case+' About '+primary_keyword+' ',
                _token: token,
                length: 269*parseInt(variants)
            };

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (response) {
                    selector.removeClass('d-none');
                    loader_selector.addClass('d-none');
                    $('.ai_description').summernote("code", response.content);
                },
                error: function (error) {
                    selector.removeClass('d-none');
                    loader_selector.addClass('d-none');
                    alert('Something went wrong, please try again later');
                },
                fail: function (error) {
                    selector.removeClass('d-none');
                    loader_selector.addClass('d-none');
                    alert('Something went wrong, please try again later');
                }
            });
        });
    });
});