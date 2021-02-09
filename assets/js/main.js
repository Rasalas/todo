$( document ).ready(function() {



    /* // DataTable unterkuenfte
    $('#table-unterkuenfte').DataTable( {
        "language": {
            "lengthMenu": "Zeige _MENU_ Unterkünfte pro Seite",
            "zeroRecords": "Leider wurde nichts gefunden",
            "info": "Zeige Seite _PAGE_ von _PAGES_",
            "infoEmpty": "Keine Unterkünfte gefunden",
            "emptyTable": "Keine Unterkünfte gefunden",
            "loadingRecords": "Loading...",
            "processing": "Bitte warten...",
            "search": "Suche:",
            "paginate": {
                "first": "Erster",
                "last": "Letzter",
                "next": "Weiter",
                "previous": "Zurück"
            }
        }
    } ); */

    // Remove Button Unterkunft
    $(document).on('click', '.task-delete', function() {
        $link = $(this).data("link");
        var removeTask = confirm('Soll der Task wirklich gelöscht werden?');
        if(removeTask==true) {
            window.location.href = $link;
        }
    });

    // Update Button Unterkunft
    $(document).on('click', '.task-edit', function() {
        $link = $(this).data("link");
        window.location.href = $link;
    });

    // View Button Unterkunft
    $(document).on('click', '.task-time', function() {
        $link = $(this).data("link");
        window.location.href = $link;
    });

    // View Button Unterkunft
    $(document).on('click', '.todoCheck1', function() {
        alert("test");
    });

    // todo checkbox click
    /** <input type="checkbox" name="test" value="bar" /> */
    $('input[name=todo]').change(function(){
        if($(this).is(':checked')){
                //task-done
                $link = "/todo/task-done/"+$(this).attr("value");
                //console.log($link)
                window.location.href = $link;
            } else {
                //task-undone
                $link = "/todo/task-undone/"+$(this).attr("value");
                //console.log($link)
                window.location.href = $link;
            }
        });
    
/** ##################### BEISPIELE ##################### */
    // Remove Button Hostessen
    $(document).on('click', '.hostessen-confirmation', function() {
        $name = $(this).data("name");
        $link = $(this).data("link");
        var removeHostessen = confirm('Soll die Person "' + $name + '" gelöscht werden?');
        if(removeHostessen==true) {
            window.location.href = $link;
        }
    });

    // Update Button Hostessen
    $(document).on('click', '.hostessen-update', function() {
        $link = $(this).data("link");
        window.location.href = $link;
    });

    // Update Button Einsatz
    $(document).on('click', '.einsatz-update', function() {
        $link = $(this).data("link");
        window.location.href = $link;
    });

    // View Button Projekt
    $(document).on('click', '.projekt-view', function() {
        $link = $(this).data("link");
        window.location.href = $link;
    });

    // Update Button Projekt
    $(document).on('click', '.projekt-update', function() {
        $link = $(this).data("link");
        window.location.href = $link;
    });

    // Update Button Einsatz
    $(document).on('click', '.einsatz-personal', function() {
        $link = $(this).data("link");
        window.location.href = $link;
    });

    // Copy Button Einsatz
    $(document).on('click', '.einsatz-copy', function() {
        $link = $(this).data("link");
        window.location.href = $link;
    });

    // Remove Button Einsatz
    $(document).on('click', '.einsatz-loeschen', function() {
        $name = $(this).data("name");
        $link = $(this).data("link");
        var removeEinsatz = confirm('Soll der Einsatz gelöscht werden?');
        if(removeEinsatz==true) {
            window.location.href = $link;
        }
    });

    // Update Button Kunden
    $(document).on('click', '.bewerber-update', function() {
        $link = $(this).data("link");
        window.location.href = $link;
    });

    // Remove Button Einsatz
    $(document).on('click', '.bewerber-loeschen', function() {
        $name = $(this).data("bewerbername");
        $link = $(this).data("link");
        var removeBewerber = confirm('Soll der Bewerber "' + $name + '" gelöscht werden?');
        if(removeBewerber==true) {
            window.location.href = $link;
        }
    });

    // Remove Button Preson von Einsatz
    $(document).on('click', '.personal-loeschen', function() {
        $name = $(this).data("name");
        $link = $(this).data("link");
        var removeEinsatz = confirm('Soll der Promoter vom Einsatz gelöscht werden?');
        if(removeEinsatz==true) {
            window.location.href = $link;
        }
    });

    // Remove Button Preson von Einsatz
    $(document).on('click', '.projekt-loeschen', function() {
        $name = $(this).data("projektname");
        $link = $(this).data("link");
        var removeProjekt = confirm('Soll das Projekt "' + $name + '" gelöscht werden?');
        if(removeProjekt==true) {
            window.location.href = $link;
        }
    });

    

});
