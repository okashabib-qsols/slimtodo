$(document).ready(function () {

    // $('#loader').show()

    $("#list").sortable({
        axis: 'y',
        update: function (e, ui) {
            console.log($(e.target), ui.items)

            // var data = $(this).sortable('toArray', {
            //     attribute: 'id'
            // });
            // if (!data) return;
            // $.ajax({
            //     method: "POST",
            //     url: "actions/updateposition.php",
            //     dataType: "json",
            //     data: {
            //         itemPosition: data.join(',')
            //     },
            //     success: function (response) {
            //         if (response.success) {
            //             Toastify({
            //                 text: response.message,
            //                 duration: 3000,
            //                 stopOnFocus: true,
            //                 position: "right",
            //                 style: {
            //                     borderRadius: "10px",
            //                 },
            //                 offset: {
            //                     y: 30
            //                 },
            //             }).showToast();
            //         } else if (!response.success) {
            //             Toastify({
            //                 text: response.message,
            //                 duration: 3000,
            //                 stopOnFocus: true,
            //                 position: "right",
            //                 style: {
            //                     background: "red",
            //                     borderRadius: "10px",
            //                 },
            //                 offset: {
            //                     y: 30
            //                 },
            //             }).showToast();
            //         }
            //     }
            // });
        }
    });

    $('#add-new').submit(function (e) {
        e.preventDefault()
        $('#add-new-submit').attr('disabled', true).html($('#loader').show())
        var description = $('#description').val()
        let formData = { description: description }
        $.ajax({
            method: "POST",
            url: "http://localhost:8081/todos",
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: (response) => {
                if (response.success) {
                    Toastify({
                        text: response.message,
                        duration: 3000,
                        stopOnFocus: true,
                        position: "right",
                        style: {
                            borderRadius: "10px",
                        },
                        offset: {
                            y: 30
                        },
                    }).showToast();

                    let rel = $('#list .list').length
                    $('#list').append(
                        `
                        <li color="1" class="colorBlue list" rel=${rel} id=${response.data.id}>
                            <span id="${response.data.id}listitem" title="Double-click to edit...">${response.data.description}</span>
                            <div class="draggertab tab"></div>
                            <div class="colortab tab"></div>
                            <div class="deletetab tab" title="Double click to delete"></div>
                            <div class="donetab tab"></div>
                        </li>
                        `
                    )

                    $('#add-new')[0].reset()
                    $('#add-new-submit').attr('disabled', false).html('Add')
                } else if (!response.success) {
                    Toastify({
                        text: response.message,
                        duration: 3000,
                        stopOnFocus: true,
                        position: "right",
                        style: {
                            background: "red",
                            borderRadius: "10px",
                        },
                        offset: {
                            y: 30
                        },
                    }).showToast();
                }
            }
        });
    })

    $('#list').on('dblclick', '.donetab', function () {
        let list = $(this).closest('.list');
        let span = list.find('span')
        let rowId = list.attr('id').split('_')[1]
        console.log(rowId)
        if (!rowId) return
        if (span.find('img').length > 0) {
            return;
        }

        $.ajax({
            method: 'PUT',
            url: 'http://localhost:8081/todos/' + rowId,
            data: {
                is_done: 1
            },
            success: function (res) {
                console.log(res)
                if (res.success) {
                    list.css({
                        opacity: '0.5'
                    })
                    span.append(
                        `
                            <img src="/images/crossout.png" class="crossout" style="width: 100%; display: block;" />
                        `
                    )
                } else if (!res.success) {
                    Toastify({
                        text: res.message,
                        duration: 3000,
                        stopOnFocus: true,
                        position: "right",
                        style: {
                            background: "red",
                            borderRadius: "10px",
                        },
                        offset: {
                            y: 30
                        },
                    }).showToast();
                }
            },
            error: function (xhr, status, err) {
                console.error(status, err)
                Toastify({
                    text: "Error while updaing",
                    duration: 3000,
                    stopOnFocus: true,
                    position: "right",
                    style: {
                        background: "red",
                        borderRadius: "10px",
                    },
                    offset: {
                        y: 30
                    },
                }).showToast();
            }
        })
    })

    $('#list').on('mouseenter', '.deletetab', function () {
        $(this).css({
            width: '44px',
            display: 'block',
            right: '-64px'
        });
    }).on('mouseleave', '.deletetab', function () {
        $(this).css({
            width: '',
            display: '',
            right: ''
        });
    });

    $('#list').on('dblclick', 'span', function () {
        let spanValue = $(this).text();
        if (!spanValue) return
        $(this).html(
            `
                <input type="text" class="editDescription" value=${spanValue} />
                <button class="saveBtn">Save</button>
            `
        )
    });

    $('#list').on('click', '.saveBtn', function () {
        let row = $(this).closest('.list')
        let rowId = row.attr('id').split('_')[1]
        if (!rowId) {
            return
        }
        let inputVal = row.find('.editDescription').val()
        row.find('span').html(inputVal)
        $(this).attr('disabled', true).html(
            $('#loader').show()
        );

        $.ajax({
            method: 'PUT',
            url: 'http://localhost:8081/todos/' + rowId,
            data: {
                description: inputVal
            },
            dataType: 'json',
            success: function (res) {
                console.log(res)
                if (res.success) {
                    Toastify({
                        text: res.message,
                        duration: 3000,
                        stopOnFocus: true,
                        position: "right",
                        style: {
                            borderRadius: "10px",
                        },
                        offset: {
                            y: 30
                        },
                    }).showToast();
                    $(this).attr('disabled', false).html(
                        `<button class="saveBtn">Save</button>`
                    );
                } else if (!res.success) {
                    Toastify({
                        text: res.message,
                        duration: 3000,
                        stopOnFocus: true,
                        position: "right",
                        style: {
                            background: "red",
                            borderRadius: "10px",
                        },
                        offset: {
                            y: 30
                        },
                    }).showToast();
                    $(this).attr('disabled', false).html(
                        `<button class="saveBtn">Save</button>`
                    );
                }
            },
            error: function (xhr, status, err) {
                console.error(status, err)
                Toastify({
                    text: 'Error while updating...',
                    duration: 3000,
                    stopOnFocus: true,
                    position: 'right',
                    style: {
                        background: 'red',
                        borderRadius: '10px',
                    },
                    offset: { y: 30 },
                }).showToast();
            }
        })
    })

    $('#list').on('click', '.colortab', function () {
        let rowId = $(this).closest('.list').attr('id').split('_')[1]
        if (!rowId) return;
        if ($(this).find('.colorpicker').length == 0) {
            let colorPicker = $('<input class="colorpicker" type="color" />')
            $(this).append(colorPicker)

            let colorVal;

            colorPicker.on('input', function () {
                colorVal = $(this).val()
                console.log(colorVal)
                $(this).closest('li').find('span').css({
                    background: colorVal
                })
            })

            colorPicker.on('change', function () {
                $.ajax({
                    method: 'PUT',
                    url: 'http://localhost:8081/todos/' + rowId,
                    data: {
                        color: colorVal
                    },
                    dataType: 'json',
                    success: function (res) {
                        console.log(res)
                        if (res.success) {
                            Toastify({
                                text: res.message,
                                duration: 3000,
                                stopOnFocus: true,
                                position: "right",
                                style: {
                                    borderRadius: "10px",
                                },
                                offset: {
                                    y: 30
                                },
                            }).showToast();

                            $('.colorpicker').remove();

                        } else if (!res.success) {
                            Toastify({
                                text: res.message,
                                duration: 3000,
                                stopOnFocus: true,
                                position: "right",
                                style: {
                                    background: "red",
                                    borderRadius: "10px",
                                },
                                offset: {
                                    y: 30
                                },
                            }).showToast();
                        }
                    },
                    error: function (xhr, status, err) {
                        console.error(status, err)
                        Toastify({
                            text: 'Error updating color',
                            duration: 3000,
                            stopOnFocus: true,
                            position: 'right',
                            style: {
                                background: 'red',
                                borderRadius: '10px',
                            },
                            offset: { y: 30 },
                        }).showToast();
                    }
                })
            });
        }
    })

    $('#list').on('dblclick', '.deletetab', function () {
        let list = $(this).closest('.list');
        let rowId = list.attr('id').split('_')[1]
        if (!rowId) return
        $('#loader').show()
        $.ajax({
            method: 'DELETE',
            url: 'http://localhost:8081/todos/' + rowId,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    Toastify({
                        text: res.message,
                        duration: 3000,
                        stopOnFocus: true,
                        position: "right",
                        style: {
                            borderRadius: "10px",
                        },
                        offset: {
                            y: 30
                        },
                    }).showToast();

                    $('#loader').hide()
                    list.remove();
                } else if (!res.success) {
                    Toastify({
                        text: res.message,
                        duration: 3000,
                        stopOnFocus: true,
                        position: "right",
                        style: {
                            background: "red",
                            borderRadius: "10px",
                        },
                        offset: {
                            y: 30
                        },
                    }).showToast();
                    $('#loader').hide()
                }
            },
            error: function (xhr, status, err) {
                console.error(status, err)
                Toastify({
                    text: 'Error while deleting',
                    duration: 3000,
                    stopOnFocus: true,
                    position: 'right',
                    style: {
                        background: 'red',
                        borderRadius: '10px',
                    },
                    offset: { y: 30 },
                }).showToast();
            }
        })
    });
})