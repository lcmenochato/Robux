function initSparkline() {
    var e = function() {
            for (var e = new Array(20), t = 0; t < e.length; t++) e[t] = [5 + n(), 10 + n(), 15 + n(), 20 + n(), 30 + n(), 35 + n(), 40 + n(), 45 + n(), 50 + n()];
            return e
        }(),
        t = {
            type: "bar",
            barWidth: 3,
            height: 15,
            barColor: "#f46b45"
        };

    function n() {
        return Math.floor(80 * Math.random())
    }
    
    if ($("#mini-bar-chart1").length) $("#mini-bar-chart1").sparkline(e[0], t);
    t.barColor = "#2c83b6";
    if ($("#mini-bar-chart2").length) $("#mini-bar-chart2").sparkline(e[1], t);
    t.barColor = "#61bda1";
    if ($("#mini-bar-chart3").length) $("#mini-bar-chart3").sparkline(e[2], t);
    
    if ($(".sparkline").length) {
        $(".sparkline").each(function() {
            var e = $(this);
            if (e.data()) e.sparkline("html", e.data());
        });
    }
    
    if ($(".sparkbar").length) {
        $(".sparkbar").sparkline("html", {
            type: "bar"
        });
    }
}

function skinChanger() {
    function saveThemeSettings() {
        var theme = $(".choose-skin li.active").data("theme");
        var sidebarLight = $(".sidebar_light input").is(":checked");
        var gradientMode = $(".gradient_mode input").is(":checked");
        var darkMode = $(".dark_mode input").is(":checked");
        var rtlMode = $(".rtl_mode input").is(":checked");

        var settings = {
            theme: theme || "blue",
            sidebarLight: sidebarLight,
            gradientMode: gradientMode,
            darkMode: darkMode,
            rtlMode: rtlMode
        };

        try {
            localStorage.setItem("themeSettings", JSON.stringify(settings));
        } catch (e) {}
    }

    function updateThemeIcon() {
        var icon = document.getElementById("icone-do-tema");
        if (!icon) return;

        var currentTheme = document.documentElement.getAttribute("data-theme");

        if (currentTheme === "dark") {
            icon.src = "https://i.imgur.com/LcpmKCe.png";
        } else {
            icon.src = "https://i.imgur.com/VcTOBzi.png";
        }
    }

    function loadThemeSettings() {
        var settings;
        try {
            settings = localStorage.getItem("themeSettings");
            if (settings) {
                settings = JSON.parse(settings);
            }
        } catch (e) {
            return;
        }

        if (settings) {
            if (settings.theme) {
                $(".choose-skin li").removeClass("active");
                var themeElement = $(".choose-skin li[data-theme='" + settings.theme + "']");
                if (themeElement.length) {
                    themeElement.addClass("active");
                    var bodyElement = $("#body");
                    if (bodyElement.length) {
                        bodyElement.removeClass("theme-green theme-orange theme-blush theme-cyan theme-timber theme-blue theme-amethyst").addClass("theme-" + settings.theme);
                    } else {
                        $("body").removeClass("theme-green theme-orange theme-blush theme-cyan theme-timber theme-blue theme-amethyst").addClass("theme-" + settings.theme);
                    }
                }
            }

            if (settings.sidebarLight !== undefined) {
                $(".sidebar_light input").prop("checked", settings.sidebarLight);
                if (settings.sidebarLight) {
                    $(".sidebar").addClass("light_active");
                } else {
                    $(".sidebar").removeClass("light_active");
                }
            }

            if (settings.gradientMode !== undefined) {
                $(".gradient_mode input").prop("checked", settings.gradientMode);
                if (settings.gradientMode) {
                    $(".theme-bg").addClass("gradient");
                } else {
                    $(".theme-bg").removeClass("gradient");
                }
            }

            if (settings.darkMode !== undefined) {
                $(".dark_mode input").prop("checked", settings.darkMode);
                if (settings.darkMode) {
                    document.documentElement.setAttribute("data-theme", "dark");
                } else {
                    document.documentElement.setAttribute("data-theme", "light");
                }
            }

            if (settings.rtlMode !== undefined) {
                $(".rtl_mode input").prop("checked", settings.rtlMode);
                if (settings.rtlMode) {
                    $("body").addClass("rtl_active");
                } else {
                    $("body").removeClass("rtl_active");
                }
            }
        }

        updateThemeIcon();
    }

    if ($(".choose-skin li").length) {
        $(".choose-skin li").on("click", function() {
            var newTheme = $(this).data("theme");
            $(".choose-skin li").removeClass("active");
            $(this).addClass("active");

            var bodyElement = $("#body");
            if (bodyElement.length) {
                bodyElement.removeClass("theme-green theme-orange theme-blush theme-cyan theme-timber theme-blue theme-amethyst").addClass("theme-" + newTheme);
            } else {
                $("body").removeClass("theme-green theme-orange theme-blush theme-cyan theme-timber theme-blue theme-amethyst").addClass("theme-" + newTheme);
            }

            saveThemeSettings();
        });
    }

    if ($(".themesetting .theme_btn").length) {
        $(".themesetting .theme_btn").on("click", function(e) {
            e.stopPropagation();
            $(".themesetting").toggleClass("open");
        });
    }

    if ($(".rtl_mode input").length) {
        $(".rtl_mode input").on("change", function() {
            if (this.checked) {
                $("body").addClass("rtl_active");
            } else {
                $("body").removeClass("rtl_active");
            }
            saveThemeSettings();
        });
    }

    if ($(".gradient_mode input").length) {
        $(".gradient_mode input").on("change", function() {
            if (this.checked) {
                $(".theme-bg").addClass("gradient");
            } else {
                $(".theme-bg").removeClass("gradient");
            }
            saveThemeSettings();
        });
    }

    if ($(".sidebar_light input").length) {
        $(".sidebar_light input").on("change", function() {
            if (this.checked) {
                $(".sidebar").addClass("light_active");
            } else {
                $(".sidebar").removeClass("light_active");
            }
            saveThemeSettings();
            updateThemeIcon();
        });
    }

    if ($(".dark_mode input").length) {
        $(".dark_mode input").on("change", function() {
            if (this.checked) {
                document.documentElement.setAttribute("data-theme", "dark");
            } else {
                document.documentElement.setAttribute("data-theme", "light");
            }
            saveThemeSettings();
            updateThemeIcon();
        });
    }

    loadThemeSettings();
}

function alterar_tema(botao) {
    var settings;

    try {
        settings = localStorage.getItem("themeSettings");
        settings = settings ? JSON.parse(settings) : {};
    } catch (e) {
        settings = {};
    }

    var isDark = document.documentElement.getAttribute("data-theme") === "dark";

    if (isDark) {
        document.documentElement.setAttribute("data-theme", "light");
        $(".sidebar").addClass("light_active");
        $(".dark_mode input").prop("checked", false);
        $(".sidebar_light input").prop("checked", true);
        settings.darkMode = false;
        settings.sidebarLight = true;
    } else {
        document.documentElement.setAttribute("data-theme", "dark");
        $(".sidebar").removeClass("light_active");
        $(".dark_mode input").prop("checked", true);
        $(".sidebar_light input").prop("checked", false);
        settings.darkMode = true;
        settings.sidebarLight = false;
    }

    try {
        localStorage.setItem("themeSettings", JSON.stringify(settings));
    } catch (e) {}

    var icon = document.getElementById("icone-do-tema");
    if (icon) {
        if (document.documentElement.getAttribute("data-theme") === "dark") {
            icon.src = "https://i.imgur.com/LcpmKCe.png";
        } else {
            icon.src = "https://i.imgur.com/VcTOBzi.png";
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    var icon = document.getElementById("icone-do-tema");
    if (icon) {
        if (document.documentElement.getAttribute("data-theme") === "dark") {
            icon.src = "https://i.imgur.com/LcpmKCe.png";
        } else {
            icon.src = "https://i.imgur.com/VcTOBzi.png";
        }
    }
});

function CustomJs() {
    if ($("#main-menu").length) {
        $("#main-menu").metisMenu();
    }
    
    if ($("#left-sidebar .sidebar-scroll").length) {
        $("#left-sidebar .sidebar-scroll").slimScroll({
            height: "calc(100vh - 65px)",
            wheelStep: 10,
            touchScrollStep: 50,
            color: "rgba(23,25,28,0.02)",
            size: "3px",
            borderRadius: "3px",
            alwaysVisible: false,
            position: "right"
        });
    }
    
    if ($(".btn-toggle-offcanvas").length) {
        $(".btn-toggle-offcanvas").on("click", function(e) {
            e.stopPropagation();
            $("body").toggleClass("offcanvas-active");
        });
    }
    
    if ($("#main-content").length) {
        $("#main-content").on("click", function() {
            $("body").removeClass("offcanvas-active");
            $(".sticky-note").removeClass("open");
        });
    }
    
    if ($(".right_toggle, .overlay").length) {
        $(".right_toggle, .overlay").on("click", function() {
            $("#rightbar").toggleClass("open");
            $(".overlay").toggleClass("open");
        });
    }
    
    if ($(".right_note").length) {
        $(".right_note").on("click", function(e) {
            e.stopPropagation();
            $(".sticky-note").toggleClass("open");
        });
    }
    
    if ($('[data-toggle="tooltip"]').length) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    if ($('[data-toggle="popover"]').length) {
        $('[data-toggle="popover"]').popover();
    }
    
    $(window).on("load", function() {
        if ($("#main-content").length && $("#left-sidebar").length && $("#footer").length) {
            if ($("#main-content").height() < $("#left-sidebar").height()) {
                $("#main-content").css("min-height", $("#left-sidebar").innerHeight() - $("footer").innerHeight());
            }
        }
    });
    
    $(window).on("load resize", function() {
        if ($(".navbar-brand").length) {
            if ($(window).innerWidth() < 420) {
                $(".navbar-brand").attr("src", "assets/images/icon.svg");
            } else {
                $(".navbar-brand").attr("src", "assets/images/logo.svg");
            }
        }
    });
    
    if ($(".full-screen").length) {
        $(".full-screen").on("click", function() {
            $(this).closest(".card").toggleClass("fullscreen");
        });
    }
    
    if (document.getElementById("btnFullscreen")) {
        document.getElementById("btnFullscreen").addEventListener("click", function() {
            var element = document.documentElement;
            if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            } else {
                if (element.requestFullscreen) {
                    element.requestFullscreen();
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                } else if (element.mozRequestFullScreen) {
                    element.mozRequestFullScreen();
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                }
            }
        });
    }
    
    if ($(".progress .progress-bar").length) {
        $(".progress .progress-bar").progressbar({
            display_text: "none"
        });
    }
    
    if ($(".header-dropdown .dropdown-toggle").length) {
        $(".header-dropdown .dropdown-toggle").on("click", function(e) {
            e.stopPropagation();
            $(this).siblings(".dropdown-menu").toggleClass("vivify fadeIn");
        });
    }
    
    if ($(".check-all").length) {
        $(".check-all").on("click", function() {
            var checkboxes = $(this).closest(".check-all-parent").find(".checkbox-tick");
            if (this.checked) {
                checkboxes.prop("checked", true);
            } else {
                checkboxes.prop("checked", false);
            }
        });
    }
    
    if ($(".checkbox-tick").length) {
        $(".checkbox-tick").on("click", function() {
            var parent = $(this).closest(".check-all-parent");
            var allCheckboxes = parent.find(".checkbox-tick");
            var checkedCheckboxes = parent.find(".checkbox-tick:checked");
            
            if (checkedCheckboxes.length === allCheckboxes.length) {
                parent.find(".check-all").prop("checked", true);
            } else {
                parent.find(".check-all").prop("checked", false);
            }
        });
    }
    
    if ($("a.mail-star").length) {
        $("a.mail-star").on("click", function(e) {
            e.preventDefault();
            $(this).toggleClass("active");
        });
    }
    
    if ($(".menu_toggle").length) {
        $(".menu_toggle").on("click", function(e) {
            e.stopPropagation();
            $("body").toggleClass("toggle_menu_active");
        });
    }
}

function SearchDiv() {
    if ($(".search-form input").length) {
        $(".search-form input").focus(function() {
            $(".recent_searche").show("slow");
        });
        
        $(".search-form input").blur(function() {
            if (!$(this).val()) {
                $(".recent_searche").hide("slow");
            }
        });
    }
}

$(function() {
    "use strict";
    skinChanger();
    initSparkline();
    CustomJs();
    SearchDiv();
    
    if ($(".page-loader-wrapper").length) {
        setTimeout(function() {
            $(".page-loader-wrapper").fadeOut();
        }, 30);
    }
});

$.fn.clickToggle = function(t, n) {
    return this.each(function() {
        var e = false;
        $(this).on("click", function() {
            if (e) {
                e = false;
                if (typeof n === "function") n.apply(this, arguments);
            } else {
                e = true;
                if (typeof t === "function") t.apply(this, arguments);
            }
        });
    });
};

var toggleSwitch = document.querySelector('.dark_mode input[type="checkbox"]');
if (toggleSwitch) {
    var currentTheme = localStorage.getItem("theme");
    
    function switchTheme(e) {
        if (e.target.checked) {
            document.documentElement.setAttribute("data-theme", "dark");
            localStorage.setItem("theme", "dark");
        } else {
            document.documentElement.setAttribute("data-theme", "light");
            localStorage.setItem("theme", "light");
        }
        
        var settings;
        try {
            settings = localStorage.getItem("themeSettings");
            if (settings) {
                settings = JSON.parse(settings);
                settings.darkMode = e.target.checked;
                localStorage.setItem("themeSettings", JSON.stringify(settings));
            }
        } catch (error) {
            console.error("Erro ao atualizar configurações:", error);
        }
    }
    
    if (currentTheme) {
        document.documentElement.setAttribute("data-theme", currentTheme);
        if (currentTheme === "dark") {
            toggleSwitch.checked = true;
        }
    }
    
    toggleSwitch.addEventListener("change", switchTheme, false);
}

var Tawk_API = Tawk_API || {};
var Tawk_LoadStart = new Date();
(function() {
    if (typeof Tawk_API === 'undefined') return;
    
    var s1 = document.createElement("script");
    var s0 = document.getElementsByTagName("script")[0];
    s1.async = true;
    s1.src = "https://embed.tawk.to/5e44175da89cda5a188591ec/1e0t1qduj";
    s1.charset = "UTF-8";
    s1.setAttribute("crossorigin", "*");
    if (s0 && s0.parentNode) {
        s0.parentNode.insertBefore(s1, s0);
    } else {
        document.head.appendChild(s1);
    }
})();