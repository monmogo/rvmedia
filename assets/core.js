$(document).ready(function() {
    $(".category-btn").click(function() {
        $(".category-btn").removeClass("active");
        $(this).addClass("active");
        var category = $(this).data("category");

        $(".theme-card").each(function() {
            var themeCategory = $(this).data("category");
            if (category === "all" || themeCategory === category) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});