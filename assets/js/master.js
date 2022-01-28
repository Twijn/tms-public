const apiUri = "https://api.twitchmodsquad.com/";

const discordRegex = /^.+#\d{4}$/;
const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

$(document).ready(function() {
    let switchStep = 0;
    let switchMax = $(".switch").find("li").length;

    setInterval(function() {
        switchStep++;
        if (switchStep >= switchMax)
            switchStep = 0;
        $(".switch li:first-child").css("margin-top", (-1.2 * switchStep) + "em");
    }, 5000);

    let disallowFurther = false;

    $("#hamburger").click(function() {
        $("body").toggleClass("nav-open");
        return false;
    });

    $("#contact-us").submit(function() {
        let error = "";

        let contactInfo = $("#contact-info").val();
        let subject = $("#subject").val();
        let body = $("#body").val();

        function addError(addErr) {
            if (error !== "") error += "<br />";
            error += addErr;
        }

        if (!contactInfo.match(discordRegex) && !contactInfo.match(emailRegex)) {
            addError("Contact info does not match Discord or Email regex.");
        }

        if (subject.length < 4 || subject.length > 40) {
            addError("Subject must be between 4 and 40 characters.");
        }

        if (body.length < 50 || subject.length > 1000) {
            addError("Body must be between 50 and 1000 characters.");
        }

        if (error !== "") {
            $("#form-response").html('<div class="error">' + error + '</div>');
            $("#form-response").slideDown(300);

            $('html,body').animate({
                scrollTop: $("#get-involved").offset().top
            }, 300);
            return false;
        }


        if (disallowFurther) return false;
        disallowFurther = true;

        $.post(apiUri + "contact-us", $("#contact-us").serialize(), function(data) {
            let resp = $("#form-response");
            if (data.success) {
                resp.html('<div class="success">Your response has been recorded.</div>');
                resp.slideDown(300);

                $("#contact-us").slideUp(300);
            } else {
                resp.html('<div class="error">' + data.error + '</div>');
                resp.slideDown(300);

                $('html,body').animate({
                    scrollTop: $("#get-involved").offset().top
                }, 300);

                disallowFurther = false;
            }
        });

        return false;
    });
});