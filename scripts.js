function checkAvailability(type, value, spanId) {
  if (value) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "check_available.php?" + type + "=" + value, true);
    xhr.onload = function () {
      if (this.status == 200) {
        var result = this.responseText;
        var messageSpan = document.getElementById(spanId);
        messageSpan.textContent = result == "0" ? "متاح" : "موجود مسبقاً";
        messageSpan.style.color = result == "0" ? "green" : "red";
      }
    };
    xhr.send();
  }
}

function validateEnglishOnly(input) {
  input.value = input.value.replace(/[^A-Za-z0-9]/g, "");
  checkAvailability("username", input.value, "usernameAvailability");
}

function validatePhoneNumber(input) {
  input.value = input.value.replace(/[^0-9]/g, "");
  if (input.value.length > 10) {
    input.value = input.value.substring(0, 10);
  }
  checkAvailability("mobile", input.value, "mobileAvailability");
}

function checkEmailAvailability() {
  var email = document.getElementById("user_email").value;
  checkAvailability("email", email, "emailAvailability");
}

document.addEventListener("DOMContentLoaded", function () {
  var stadiumTypeSelect = document.getElementById("stadium_type");
  var stadiumSizeContainer = document.getElementById("stadiumSizeContainer");

  stadiumTypeSelect.addEventListener("change", function () {
    if (this.value === "قدم") {
      stadiumSizeContainer.style.display = "";
    } else {
      stadiumSizeContainer.style.display = "none";
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const stars = document.querySelectorAll(".star");
  stars.forEach((star) => {
    star.addEventListener("click", function (e) {
      setRating(e);
      submitRatingForm();
    });
  });

  function setRating(e) {
    const ratingValue = e.target.getAttribute("data-value");
    document.getElementById("rating").value = ratingValue;

    stars.forEach((star) => {
      if (star.getAttribute("data-value") <= ratingValue) {
        star.classList.add("checked");
      } else {
        star.classList.remove("checked");
      }
    });
  }
});

function submitRatingForm() {
  var formData = new FormData(document.getElementById("ratingForm"));
  fetch("add_rating.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("averageRatingDisplay").innerText =
          data.averageRating;
        document.getElementById("totalRatingsDisplay").innerText =
          data.totalRatings;
      } else {
        alert("حدث خطأ أثناء إضافة التقييم");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
  return false;
}

function updateRatings(averageRating, totalRatings) {
  document.getElementById("averageRating").textContent = averageRating;
  document.getElementById("totalRatings").textContent = totalRatings;
}

function editComment(commentId) {
  console.log("Comment ID:", commentId);
  var commentTextElement = document.getElementById("commentText_" + commentId);
  console.log("Comment Text Element:", commentTextElement);
  if (commentTextElement) {
    var commentText = commentTextElement.textContent;
    document.getElementById("editCommentText").value = commentText;
    document.getElementById("editCommentId").value = commentId;
    $("#editCommentModal").modal("show");
  } else {
    alert("تعذر العثور على التعليق.");
  }
}

function deleteComment(commentId) {
  if (confirm("هل أنت متأكد من حذف هذا التعليق؟")) {
    fetch("delete_comment.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "comment_id=" + commentId,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          var commentElement = document.getElementById("comment_" + commentId);
          if (commentElement) commentElement.remove();
        } else {
          alert("حدث خطأ أثناء حذف التعليق.");
        }
      });
  }
}

function submitEditComment() {
  var commentId = document.getElementById("editCommentId").value;
  var updatedComment = document.getElementById("editCommentText").value;
  fetch("edit_comment.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body:
      "comment_id=" +
      commentId +
      "&comment_text=" +
      encodeURIComponent(updatedComment),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        var commentTextElement = document.getElementById(
          "commentText_" + commentId
        );
        if (commentTextElement) commentTextElement.textContent = updatedComment;

        $("#editCommentModal").modal("hide");
      } else {
        alert("حدث خطأ أثناء تحديث التعليق.");
      }
    });
}

function setEndTime() {
  var startTime = document.getElementById("booking_time_start").value;

  if (startTime) {
    var endTime = new Date();
    endTime.setHours(
      parseInt(startTime.split(":")[0]) + 2,
      parseInt(startTime.split(":")[1])
    );

    var hours = endTime.getHours().toString().padStart(2, "0");
    var minutes = endTime.getMinutes().toString().padStart(2, "0");

    document.getElementById("booking_time_end").value = hours + ":" + minutes;
  }
}
