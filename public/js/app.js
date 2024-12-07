$(document).ready(function () {

    $("#list").sortable({
        axis: 'y',
        revert: true,
        cursor: "move",
        update: function (e, ui) {

            $('#overlay').show();
            $('#loader').show();

            var data = $(this).sortable('toArray', {
                attribute: 'id'
            });
            if (!data) return;

            var itemPositions = data.map(function (id, index) {
                return {
                    id: id.split('_')[1],
                    position: index + 1
                };
            });

            $.ajax({
                method: "PUT",
                url: "http://localhost:8080/todos",
                headers: {
                    'csrf_name': $('#csrf_name').val(),
                    'csrf_value': $('#csrf_value').val()
                },
                contentType: "application/json",
                dataType: "json",
                data: JSON.stringify({
                    position: itemPositions
                }),
                success: function (response) {
                    console.log(response)
                    if (response?.success) {
                        Toastify({
                            text: response?.message,
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
                        $('#csrf_name').val(response?.csrf?.name);
                        $('#csrf_value').val(response?.csrf?.value);
                    } else if (!response?.success) {
                        Toastify({
                            text: response?.message,
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
                    $('#loader').hide();
                    $('#overlay').hide();

                }, error: function (x, s, e) {
                    console.error(x, s, e)
                    Toastify({
                        text: "Something went wrong",
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
                    $('#loader').hide();
                    $('#overlay').hide();
                }
            });
        }
    });

    $('#add-new').submit(function (e) {
        e.preventDefault()

        $('#add-new-submit').attr('disabled', true)
        var description = $('#description').val().trim()

        $('#overlay').show();
        $('#loader').show();

        let formData = { description: description }
        $.ajax({
            method: "POST",
            headers: {
                'csrf_name': $('#csrf_name').val(),
                'csrf_value': $('#csrf_value').val()
            },
            contentType: 'application/json',
            url: "http://localhost:8080/todos",
            data: JSON.stringify(formData),
            success: (response) => {
                if (response?.success) {
                    Toastify({
                        text: response?.message,
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

                    $('#list').append(
                        `
                        <li color="1" class="color-blue list" id="todo_${response?.data?.id}">
                            <span id="${response?.data?.id}listitem" title="Double-click to edit...">${response?.data?.description}</span>
                            <button class="draggertab tab" aria-label="Drag Todo"></button>
                            <button class="colortab tab" aria-label="Change Color"></button>
                            <button class="deletetab tab" aria-label="Delete Todo"></div>
                            <button class="donetab tab" aria-label="Mark as Done"></button>
                        </li>
                        `
                    )

                    $('#csrf_name').val(response?.csrf?.name);
                    $('#csrf_value').val(response?.csrf?.value);

                    $('#add-new')[0].reset()
                    $('#add-new-submit').attr('disabled', false)
                } else if (!response?.success) {
                    Toastify({
                        text: response?.message,
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
                $('#overlay').hide();
                $('#loader').hide();
                $('#add-new-submit').attr('disabled', false)
            },
            error: function (x, s, e) {
                console.error(x, s, e)
                $('#add-new-submit').attr('disabled', false)
                Toastify({
                    text: x?.responseJSON?.message || "Something went wrong",
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
                $('#overlay').hide();
                $('#loader').hide();
                $('#add-new-submit').attr('disabled', false)
                $('#csrf_name').val(x?.responseJSON?.csrf?.name);
                $('#csrf_value').val(x?.responseJSON?.csrf?.value);
            }
        });
    })

    $('#list').on('dblclick', '.donetab', function () {
        let list = $(this).closest('.list');
        let span = list.find('span')
        let rowId = list.attr('id').split('_')[1]
        if (!rowId) return
        if (span.find('img').length > 0) {
            return;
        }
        $('#overlay').show();
        $('#loader').show();

        $.ajax({
            method: 'PUT',
            url: 'http://localhost:8080/todos/' + rowId,
            headers: {
                'csrf_name': $('#csrf_name').val(),
                'csrf_value': $('#csrf_value').val()
            },
            data: {
                is_done: 1
            },
            success: function (response) {
                if (response?.success) {
                    list.addClass('completed')

                    span.append(
                        `
                            <img src="/images/crossout.png" class="crossout" />
                        `
                    )
                    Toastify({
                        text: "Marked as Done!",
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
                    $('#csrf_name').val(response?.csrf?.name);
                    $('#csrf_value').val(response?.csrf?.value);
                } else if (!response?.success) {
                    Toastify({
                        text: response?.message,
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
                $('#overlay').hide();
                $('#loader').hide();
            },
            error: function (xhr, status, err) {
                console.error(xhr, status, err)
                Toastify({
                    text: "Something went wrong",
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
                $('#overlay').hide();
                $('#loader').hide();
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
        $('#overlay').show();
        $('#loader').show();
        $(this).attr('disabled', true)

        $.ajax({
            method: 'PUT',
            url: 'http://localhost:8080/todos/' + rowId,
            headers: {
                'csrf_name': $('#csrf_name').val(),
                'csrf_value': $('#csrf_value').val()
            },
            data: {
                description: inputVal
            },
            dataType: 'json',
            success: function (response) {
                if (response?.success) {
                    Toastify({
                        text: response?.message,
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
                    $('#csrf_name').val(response?.csrf?.name);
                    $('#csrf_value').val(response?.csrf?.value);
                } else if (!response?.success) {
                    Toastify({
                        text: response?.message,
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
                $('#overlay').hide();
                $('#loader').hide();
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
                $('#overlay').hide();
                $('#loader').hide();
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
                $(this).closest('li').find('span').css({
                    background: colorVal
                })
            })

            colorPicker.on('change', function () {
                $('#overlay').show();
                $('#loader').show();
                $.ajax({
                    method: 'PUT',
                    url: 'http://localhost:8080/todos/' + rowId,
                    headers: {
                        'csrf_name': $('#csrf_name').val(),
                        'csrf_value': $('#csrf_value').val()
                    },
                    data: {
                        color: colorVal
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response?.success) {
                            Toastify({
                                text: response?.message,
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
                            $('#csrf_name').val(response?.csrf?.name);
                            $('#csrf_value').val(response?.csrf?.value);

                        } else if (!response?.success) {
                            Toastify({
                                text: response?.message,
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
                        $('#overlay').hide();
                        $('#loader').hide();
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
                        $('#overlay').hide();
                        $('#loader').hide();
                    }
                })
            });
        }
    })

    $('#list').on('dblclick', '.deletetab', function () {
        let list = $(this).closest('.list');
        let rowId = list.attr('id').split('_')[1]
        if (!rowId) return
        $('#overlay').show();
        $('#loader').show();
        $.ajax({
            method: 'DELETE',
            url: 'http://localhost:8080/todos/' + rowId,
            headers: {
                'csrf_name': $('#csrf_name').val(),
                'csrf_value': $('#csrf_value').val()
            },
            dataType: 'json',
            success: function (response) {
                if (response?.success) {
                    Toastify({
                        text: response?.message,
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
                    $('#csrf_name').val(response?.csrf?.name);
                    $('#csrf_value').val(response?.csrf?.value);
                } else if (!response?.success) {
                    Toastify({
                        text: response?.message,
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
                $('#overlay').hide();
                $('#loader').hide();
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
                $('#overlay').hide();
                $('#loader').hide();
            }
        })
    });
})