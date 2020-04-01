(function() {
    'use strict';

    let modal = {
        selector: document.getElementById("imgOverlay"),
        close: function () {
            modal.selector.classList.remove('shown');
        },
        open: function(ev) {
            ev.preventDefault();

            let img = ev.target;
            let link = img.parentNode;

            let pictureLink = link.href;
            let imgLink = img.getAttribute('data-large');
            let caption = img.getAttribute('data-info');

            // Cleaning IMG div
            let targetImg = modal.selector.querySelector("img#img");
            let parentContainer = targetImg.parentNode;
            parentContainer.removeChild(targetImg);

            let newImg = document.createElement('img');
            newImg.classList.add('modal-img');
            newImg.id = "img";

            newImg.src = imgLink;
            newImg.alt = caption;

            parentContainer.appendChild(newImg);

            modal.selector.querySelector(".caption>#title").innerHTML = caption;
            modal.selector.querySelector(".caption>#picture").href = pictureLink;

            modal.selector.classList.add("shown");
        }
    };

    modal.selector.onclick = modal.close;

    Array.from(document.querySelectorAll("td>a>img"))
        .forEach(function(s) {
            s.onclick = modal.open;
        });

    $(document).ready(function() {
        $(".delImg").click(function(event) {

            let id = $(this).attr("id");
            let slug = $(this).attr("data-slug");

            if (confirm('Supprimer le commentaire '+ id +' ?')) {
                $.ajax({
                    type: "POST",
                    url: "/di",
                    data: {slug: slug},
                    success: function () {
                        $("#post_"+id).remove();
                        $("#flash").load(location.href + " #flash");
                    }
                });

            }

        });
    });
})();