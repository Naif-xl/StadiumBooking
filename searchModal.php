<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>Stadium Booking</title>

    <style>
        .icon {
            padding: 5px;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- نافذة البحث المنبثقة -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel">نتائج البحث</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="modalSearchTerm" class="form-control" placeholder="ابحث هنا..."
                        oninput="searchStadiums()">
                    <div id="modalSearchResults" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openSearchModal() {
            var searchModalElement = document.getElementById('searchModal');
            if (searchModalElement) {
                var searchModal = new bootstrap.Modal(searchModalElement);
                searchModal.show();
            } else {
                console.error('Element #searchModal not found');
            }
        }

        function searchStadiums() {
            var searchTerm = document.getElementById('modalSearchTerm').value;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'search.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

            xhr.onload = function () {
                if (this.status == 200) {
                    document.getElementById('modalSearchResults').innerHTML = this.responseText;
                }
            };

            if (searchTerm.trim() !== "") {
                xhr.send('searchTerm=' + encodeURIComponent(searchTerm));
            } else {
                document.getElementById('modalSearchResults').innerHTML = "";
            }
        }

        document.getElementById('modalSearchResults').addEventListener('click', function (event) {
            var target = event.target;
            while (target != null) {
                if (target.classList.contains('stadium-card')) {
                    var stadiumId = target.getAttribute('data-stadium-id');
                    if (stadiumId) {
                        window.location.href = 'stadium.php?id=' + stadiumId;
                    }
                    break;
                }
                target = target.parentElement;
            }
        });
    </script>
</body>

</html>