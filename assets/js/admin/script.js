$ = jQuery;

function updateRecord() {
  $(".update_record").click(function () {
    $(this).find(".asos_loader").css("display", "inline-block");
    $(".asos-table tbody tr").css({
      opacity: 0.5,
      pointerEvents: "none",
    });
    const recordID = $(this).data("record_id");
    $.ajax({
      url: asosAdmin.ajax,
      type: "POST",
      data: {
        action: "asos_update_record",
        recordID: recordID,
        recordStatus: $(`#order_status_${recordID}`).val(),
      },
      success: function (res) {
        $(".asos_loader").hide();
        $(".asos-table tbody tr").css({
          opacity: 1,
          pointerEvents: "all",
        });
        alert("Record Updated");
      },
      error: function (err) {
        alert(err.responseText);
      },
    });
  });
}

function deleteRecord() {
  $(".delete_record").click(function () {
    $(this).find(".asos_loader").css("display", "inline-block");
    $(".asos-table tbody tr").css({
      opacity: 0.5,
      pointerEvents: "none",
    });

    const recordID = $(this).data("record_id");
    $.ajax({
      url: asosAdmin.ajax,
      type: "POST",
      data: {
        action: "asos_delete_record",
        recordID: recordID,
      },
      success: function (res) {
        $(`tr[data-row_id="${res.recordID}"]`).fadeOut(200);
        $(".asos_loader").hide();
        $(".asos-table tbody tr").css({
          opacity: 1,
          pointerEvents: "all",
        });
      },
      error: function (error) {
        alert(error.responseText);
      },
    });
  });
}

function filterRecord() {
  $(".filter_record").click(function () {
    const trackID = $("#asos-filter-trackid").val();
    const status = $("#asos-filter-status").val();
    const dateFrom = $("#asos-filter-dateFrom").val();
    const dateTo = $("#asos-filter-dateTo").val();
    const rows = document.querySelectorAll(".asos-table tbody tr");
    
    console.log(`.asos-table tbody tr[data-trackID="${trackID}"]`)

    if (trackID) {
      $(".asos-table tbody tr").hide();
      $(`.asos-table tbody tr[data-trackID="${trackID}"]`).show();
    }

    if (status) {
      $(".asos-table tbody tr").hide();
      $(`.asos-table tbody tr[data-status="${status}"]`).show();
    }

    if (dateFrom) {
      $(".asos-table tbody tr").hide();
      if (rows.length > 0) {
        rows.forEach((item, index) => {
          if (item.dataset.date >= dateFrom) {
            $(item).show();
          }
        });
      }
    }

    if (dateTo) {
      $(".asos-table tbody tr").hide();
      if (rows.length > 0) {
        rows.forEach((item, index) => {
          if (item.dataset.date <= dateTo) {
            $(item).show();
          }
        });
      }
    }

    if (trackID == '' && status == '' && dateFrom == '' && dateTo == '') {
      $(".asos-table tbody tr").show();
    }

  });
}

$(document).ready(function () {
  updateRecord();

  deleteRecord();

  filterRecord();
});
