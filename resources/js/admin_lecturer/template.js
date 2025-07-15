import $ from "jquery";

$(function() {
  "use strict";

  var body = $("body");
  var sidebar = $(".sidebar");

  // Enable feather-icons with SVG markup
  feather.replace();

  // Sidebar toggle to sidebar-folded
  $(".sidebar-toggler").on("click", function(e) {
    e.preventDefault();

    $(".sidebar-header .sidebar-toggler").toggleClass("active not-active");

    if (window.matchMedia("(min-width: 992px)").matches) {
      e.preventDefault();
      body.toggleClass("sidebar-folded");
    } else if (window.matchMedia("(max-width: 991px)").matches) {
      e.preventDefault();
      body.toggleClass("sidebar-open");
    }
  });

  //  Open sidebar-folded when hover
  $(".sidebar .sidebar-body").on(
    "hover",
    function() {
      if (body.hasClass("sidebar-folded")) {
        body.addClass("open-sidebar-folded");
      }
    },
    function() {
      if (body.hasClass("sidebar-folded")) {
        body.removeClass("open-sidebar-folded");
      }
    }
  );

  // Close other submenu in sidebar on opening any
  sidebar.on("show.bs.collapse", ".collapse", function() {
    sidebar.find(".collapse.show").collapse("hide");
  });

  // Close sidebar when click outside on mobile/table
  $(document).on("click touchstart", function(e) {
    e.stopPropagation();

    // Closing of sidebar menu when clicking outside of it
    if (!$(e.target).closest(".sidebar-toggler").length) {
      var sidebar = $(e.target).closest(".sidebar").length;
      var sidebarBody = $(e.target).closest(".sidebar-body").length;

      if (!sidebar && !sidebarBody) {
        if ($("body").hasClass("sidebar-open")) {
          $("body").removeClass("sidebar-open");
        }
      }
    }
  });

  // Horizontal menu in mobile
  $('[data-toggle="horizontal-menu-toggle"]').on("click", function() {
    $(".horizontal-menu .bottom-navbar").toggleClass("header-toggled");
  });

  // Horizontal menu navigation in mobile menu on click
  var navItemClicked = $(".horizontal-menu .page-navigation >.nav-item");

  navItemClicked.on("click", function() {
    if (window.matchMedia("(max-width: 991px)").matches) {
      if (!$(this).hasClass("show-submenu")) {
        navItemClicked.removeClass("show-submenu");
      }
      $(this).toggleClass("show-submenu");
    }
  });

  //Add active class to nav-link based on url dynamically
  function addActiveClass(element) {
    if (current === "") {
      // For root url
      if (element.attr("href").indexOf("index.html") !== -1) {
        element.parents(".nav-item").last().addClass("active");
        if (element.parents(".sub-menu").length) {
          element.closest(".collapse").addClass("show");
          element.addClass("active");
        }
      }
    } else {
      // For other url
      if (element.attr("href").indexOf(current) !== -1) {
        element.parents(".nav-item").last().addClass("active");
        if (element.parents(".sub-menu").length) {
          element.closest(".collapse").addClass("show");
          element.addClass("active");
        }
        if (element.parents(".submenu-item").length) {
          element.addClass("active");
        }
      }
    }
  }

  var current = location.pathname
    .split("/")
    .slice(-1)[0]
    .replace(/^\/|\/$/g, "");
  $(".nav li a", sidebar).each(function() {
    var $this = $(this);
    addActiveClass($this);
  });

  $(".horizontal-menu .nav li a").each(function() {
    var $this = $(this);
    addActiveClass($this);
  });

  // Reload Page
  $(".reloadButton").on("click", function(e) {
    e.preventDefault();
    location.reload();
  });
});
