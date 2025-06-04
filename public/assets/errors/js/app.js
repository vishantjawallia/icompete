
(function () {
    'use strict'
    $(document).ready(function() {
        JDLoader.close();
    });
    // Numbers only inputs
    document.querySelectorAll('input[integer]').forEach(function(input) {
        input.addEventListener('input', function() {
            // Replace any non-digit characters with an empty string
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    $(document).on("submit", ".ajaxForm", function (event) {
        event.preventDefault();
        var form = $(this);
        $.ajax({
            type: form.attr("method"),
            url: form.attr("action"),
            beforeSend: function () {
                JDLoader.open();
            },
            dataType: "json",
            data: form.serialize(),
            success: function (result) {
                JDLoader.close();
                // Handle success and error responses based on 'status'
                if (result.status === "success") {
                    toastr.success(result.message, "Success");
                    if (form.hasClass("autoR")) {
                        if (result.url) {
                            window.location = result.url;
                        }
                    }
                    Swal.fire("Successful!", result.message, "success").then(
                        function () {
                            if (result.url) {
                                window.location = result.url;
                            }
                        }
                    );
                    // dont clear csrf token
                    if (form.hasClass("resetForm")) {
                        var csrfToken = form.find('input[name="_token"]').val(); // Save CSRF token
                        form[0].reset(); // Reset the form
                        form.find('input[name="_token"]').val(csrfToken); // Restore CSRF token
                    }
                } else if (result.status === "error") {
                    toastr.error(result.message, "Error");
                    if (form.hasClass("autoR")) {
                        if (result.url) {
                            window.location = result.url;
                        }
                    }
                    if (result.url) {
                        Swal.fire("Error!", result.message, "warning").then(
                            function () {
                                if (result.url) {
                                    window.location = result.url;
                                }
                            }
                        );
                    }
                }
            },
            error: function (xhr) {
                JDLoader.close();
                var errors = xhr.responseJSON || {};
                toastr.error(
                    errors.message || "An unknown error occurred.",
                    "Error"
                );
                // Swal.fire("Error!", errors.message || "An unknown error occurred.", "warning");
            },
        });
    });
})()
