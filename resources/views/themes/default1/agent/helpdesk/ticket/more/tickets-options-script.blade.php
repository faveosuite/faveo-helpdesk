<script>
var filterClick = 0;
            var clearlist = 0;
            var t_id = [];
            var submit_form = 0;
            var c_status = '';
            var option = null;
            $(function () {
                //Enable check and uncheck all functionality
                $(".checkbox-toggle").click(function () {
                    var clicks = $(this).data('clicks');
                    if (clicks) {
                    //Uncheck all checkboxes
                        $(".mailbox-messages input[type='checkbox']").iCheck("uncheck");
                        $(".far", this).removeClass("fa-check-square").addClass('fa-square');
                    } else {
                    //Check all checkboxes
                        $(".mailbox-messages input[type='checkbox']").iCheck("check");
                        $(".far", this).removeClass("fa-square").addClass('fa-check-square');
                    }
                    $(this).data("clicks", !clicks);
                });
            });
            $(function () {
                // Enable check and uncheck all functionality
                $(".checkbox-toggle").click(function () {
                    var clicks = $(this).data('clicks');
                    // alert(clicks);
                    if (clicks) {
                    //Uncheck all checkboxes
                        $("input[type='checkbox']", ".mailbox-messages").iCheck("uncheck");
                        // alert($("input[type='checkbox']").val());
                        t_id = $('.selectval').map(function () {
                            return $(this).val();
                        }).get();
                        showAssign(t_id);
                        // alert(checkboxValues);
                    } else {
                    //Check all checkboxes
                        $("input[type='checkbox']", ".mailbox-messages").iCheck("check");
                        // alert('Hallo');
                        t_id = [];
                        showAssign(t_id);
                    }
                    $(this).data("clicks", !clicks);
                });
            });

            function getValues() {
                return t_id;
            }

            $(".closemodal, .no").click(function () {
                $("#myModal").css("display", "none");
                $('#myModal').modal('toggle');
            });
            
            $(".closemodal, .no").click(function () {
                $("#myModal").css("display", "none");
                $('#myModal').modal('toggle');
            });
            
            $('.yes').click(function () {
                var values = getValues();
                if (values == "") {
                    $("#myModal").css("display", "none");
                    $('#myModal').modal('toggle');
                } else {
                    $("#myModal").css("display", "none");
                    $('#myModal').modal('toggle');
                    if (c_status != 'hard-delete') {
                        var url = '{{url("select_all")}}/';
                        $.ajax({
                            type: "POST",
                            url: "{{url('select_all')}}",
                            dataType: "html",
                            data: {
                                "submit": c_status,
                                "select_all": values 
                            },
                            beforeSend: function() {
                                $('.loader1').css('display','block');
                                $('.loader').css('display','block');
                                $('#refresh').css('display','none');
                                $('#loader1').css('display','block');
                                $('#d1').prop('disabled', true);
                                $("#hidespin").addClass('d-none');
                                $("#spin").removeClass('d-none');
                                $("#hide2").addClass('d-none');
                                $("#show2").removeClass('d-none');
                            },
                            success: function(response) {
                                $('.loader1').css('display','none');
                                $('.loader').css('display','none');
                                $('#refresh').css('display','block');
                                $('#loader1').css('display','none');
                                $('#d1').prop('disabled', false);
                                $("#hide2").removeClass('d-none');
                                $("#show2").addClass('d-none');
                                $("#hidespin").removeClass('d-none');
                                $("#spin").addClass('d-none');
                                var message = "{!! Lang::get('lang.status-changed-successfully') !!} {!! Lang::get('lang.reload-be-patient-message') !!}";
                                $(".success-message, .success-msg, .get-success, #get-success").html(message);
                                $(".alert-success").removeClass('d-none');
                                setTimeout(function(){
                                    location.reload();
                                }, 3000)
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                                $('.loader1').css('display','none');
                                $('.loader').css('display','none');
                                if (xhr.status == 403) {
                                    $('#d1').prop('disabled', false);
                                    $("#hide2").removeClass('d-none');
                                    $("#show2").addClass('d-none');
                                    $("#hidespin").removeClass('d-none');
                                    $("#spin").addClass('d-none');
                                    var message = JSON.parse(xhr.responseText);
                                    $(".error-message, #get-danger").html(message.message[0]);
                                    $(".alert-danger").removeClass('d-none');
                                }
                            }
                        })
                        return false;
                    } else {
                        $("#modalpopup").unbind('submit');
                        submit_form = 1;
                        $('#hard-delete').click();
                    }
                }
            });
            
            function changeStatus(id, name) {
                $('#myModalLabel').html('{{Lang::get("lang.change-ticket-status-to")}}' + name);
                var msg = "{{Lang::get('lang.confirm-to-proceed')}}";
                var values = getValues();
                if (values == "") {
                    msg = "{{Lang::get('lang.select-ticket')}}";
                    $('.yes').html("{{Lang::get('lang.ok')}}");
                    $('#myModalLabel').html("{{Lang::get('lang.alert')}}");
                } else {
                    c_status = "Open";
                    if(id == 2){
                        c_status = "Resolve";
                    } else if (id == 3) {
                        c_status = "Close";
                    } else if(id == 5) {
                        c_status = "Delete";
                    } else if(id == 'hard-delete') {
                        c_status = "Delete forever";
                    }

                    $('.yes').html("Yes");
                }
                $('#custom-alert-body').html(msg);
                $("#myModal").css("display", "block");
                $('#myModal').modal('toggle');
            }

            $('#modalpopup').on('submit', function(e){
                if (submit_form == 0) {
                    e.preventDefault();
                    changeStatus('hard-delete', '{{Lang::get("lang.clean-forever")}}');
                }
                $('#hard-delete').val('Delete forever')
            });

            function someFunction(id) {
                if (document.getElementById(id).checked) {
                    t_id.push(id);
                    // alert(t_id);
                } else if (document.getElementById(id).checked === undefined) {
                    var index = t_id.indexOf(id);
                    if (index === - 1) {
                        t_id.push(id);
                    } else {
                        t_id.splice(index, 1);
                    }
                } else {
                    var index = t_id.indexOf(id);
                    t_id.splice(index, 1);
                    // alert(t_id);
                }
                    showAssign(t_id);
            }

            function showAssign(t_id) {
                if (t_id.length >= 1) {
                    $('#assign_Ticket').css('display', 'inline');
                } else {
                    $('#assign_Ticket').css('display', 'none');
                }
            }

        $(document).ready(function () {
            //checking merging tickets
            $('#MergeTickets').on('show.bs.modal', function () {

                // alert("hi");
                $.ajax({
                    type: "GET",
                    url: "{{route('check.merge.tickets',0)}}",
                    dataType: "html",
                    data: {data1: t_id},
                    beforeSend: function () {
                        $('.loader1').css('display','block');
                        $('.loader').css('display','block');
                        $("#merge_body").addClass('d-none');
                        $("#merge_loader").removeClass('d-none');
                    },
                    success: function (response) {
                        $('.loader1').css('display','none');
                        $('.loader').css('display','none');
                        if (response == 0) {
                            $("#merge_body").removeClass('d-none');
                            $("#merge-succ-alert").addClass('d-none');
                            $("#merge-body-alert").removeClass('d-none');
                            $("#merge-body-form").addClass('d-none');
                            $("#merge_loader").addClass('d-none');
                            $("#merge-btn").attr('disabled', true);
                            var message = "{{Lang::get('lang.select-tickets-to merge')}}";
                            $("#merge-err-alert").removeClass('d-none');
                            $('#message-merge-err').html(message);
                        } else if (response == 2) {
                            $("#merge_body").removeClass('d-none');
                            $("#merge-succ-alert").addClass('d-none');
                            $("#merge-body-alert").removeClass('d-none');
                            $("#merge-body-form").addClass('d-none');
                            $("#merge_loader").addClass('d-none');
                            $("#merge-btn").attr('disabled', true);
                            var message = "{{Lang::get('lang.different-users')}}";
                            $("#merge-err-alert").removeClass('d-none');
                            $('#message-merge-err').html(message);
                        } else {
                            $.ajax({
                                url: "{{ route('get.merge.tickets',0) }}",
                                dataType: "html",
                                data: {data1: t_id},
                                beforeSend: function(){
                                    $('.loader1').css('display','block');
                                    $('.loader').css('display','block');
                                },
                                success: function (data) {
                                    $('.loader1').css('display','none');
                                    $('.loader').css('display','none');
                                    $("#merge_body").removeClass('d-none');
                                    $("#merge-body-alert").addClass('d-none');
                                    $("#merge-body-form").removeClass('d-none');
                                    $("#merge_loader").addClass('d-none');
                                    $("#merge-btn").attr('disabled', false);
                                    $("#merge_loader").addClass('d-none');
                                    $('#select-merge-parent').html(data);
                                }
                                // return false;
                            });
                        }
                    }
                });
            });
            
            //submit merging form
            $('#merge-form').on('submit', function () {
                $.ajax({
                    type: "POST",
                    url: "{!! url('merge-tickets/') !!}/" + t_id,
                    dataType: "json",
                    data: $(this).serialize(),
                    beforeSend: function () {
                        $('.loader1').css('display','block');
                        $('.loader').css('display','block');
                        $("#merge_body").addClass('d-none');
                        $("#merge_loader").removeClass('d-none');
                    },
                    success: function (response) {
                        $('.loader1').css('display','none');
                        $('.loader').css('display','none');
                        if (response == 0) {
                            $("#merge_body").removeClass('d-none');
                            $("#merge-succ-alert").addClass('d-none');
                            $("#merge-body-alert").removeClass('d-none');
                            $("#merge-body-form").addClass('d-none');
                            $("#merge_loader").addClass('d-none');
                            $("#merge-btn").attr('disabled', true);
                            var message = "{{Lang::get('lang.merge-error')}}";
                            $("#merge-err-alert").removeClass('d-none');
                            $('#message-merge-err').html(message);
                        } else {
                            $("#merge_body").removeClass('d-none');
                            $("#merge-err-alert").addClass('d-none');
                            $("#merge-body-alert").removeClass('d-none');
                            $("#merge-body-form").addClass('d-none');
                            $("#merge_loader").addClass('d-none');
                            $("#merge-btn").attr('disabled', true);
                            var message = "{{Lang::get('lang.merge-success')}}";
                            $("#merge-succ-alert").removeClass('d-none');
                            $('#message-merge-succ').html(message);
                            setTimeout(function () {
                                $("#alert11").addClass('d-none');
                                location.reload();
                            }, 1000);
                        }
                    }
                })
                return false;
            });

            $('#AssignTickets').on('show.bs.modal', function() {
                //select_assigen_list.val(null).trigger("change");
                $("#assign_body").addClass('d-none');
                $("#assign_loader").removeClass('d-none');
                setTimeout(function(){
                    $("#assign_body").removeClass('d-none');
                    $("#assign_loader").addClass('d-none');
                }, 2000);
            });
            
            $('#assign-form').on('submit', function() {
                var t_id = "";
                $("input[name='select_all[]']:checked:enabled").each(function() {
                    if(t_id == "") {
                        t_id = $(this).val();
                    } else {
                        t_id = $(this).val() + "," + t_id;
                    }
                });
                //var t_id = $("input[name='select_all[]']").val();
                $.ajax({
                    type: "PATCH",
                    url: "{{url('ticket/assign')}}/"+t_id,
                    dataType: "html",
                    data: $(this).serialize() + '&ticket_id=' + t_id,
                    beforeSend: function() {
                        $('.loader1').css('display','block');
                        $('.loader').css('display','block');
                        $("#assign_body").addClass('d-none');
                        $("#assign_loader").removeClass('d-none');
                    },
                    success: function(response) {
                        $('.loader1').css('display','none');
                        $('.loader').css('display','none');
                        if (response == 1) {
                            var message = "{!!Lang::get('lang.ticket-assigned-successfully')!!} {!!Lang::get('lang.reload-be-patient-message')!!}"
                            $(".success-message, .success-msg, .get-success, #get-success").html(message);
                            $(".alert-success").removeClass('d-none');
                            $("#assign-close").trigger("click");
                            setTimeout(function(){
                            location.reload();
                            }, 2000)
                        }
                    },
                    error: function(){
                        $('.loader1').css('display','none');
                        $('.loader').css('display','none');
                        $("#assign_body").removeClass('d-none');
                        $("#assign_loader").addClass('d-none');
                    }
                })
                return false;
            });
        });
</script>