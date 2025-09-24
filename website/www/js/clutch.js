/* clutch.js
 *
 *
 */

"use strict";

var CLU = {
    modal: {},
    init: function() {
        const modalEl = document.getElementById('mainModal')
        let maction_button = document.getElementById('modalConfirm');
        let mtitle = document.getElementById('modalTitle');
        let mbody = document.getElementById('modalBody');

        // on modal confirm button click, determine action
        maction_button.addEventListener('click', CLU.processModal);

        // on modal appearance determine what the affirmative button will do
        modalEl.addEventListener('show.bs.modal', event => {
            const data_el = event.relatedTarget;
            const action = data_el.getAttribute('action');
            let pid, date, clutcher, ptitle;

            // this will determine action when modal affirmative is clicked
            CLU.modal.action = action;

            // customize modal
            switch (action) {
                case "delete_clutcher":
                    pid = data_el.getAttribute('pid');
                    date = data_el.getAttribute('date');
                    clutcher = data_el.getAttribute('clutcher');

                    // set data for action if carried out
                    CLU.modal.data = {
                        "pid": pid,
                        "date": date
                    };

                    // display modal text
                    mtitle.innerHTML = "Confirm clutcher erase";
                    mbody.innerHTML = 'Confirm you wish to erase clutcher <b class="accent2">' + clutcher + '</b> from the date <b class="accent3">' + date + '</b>.';
                    maction_button.innerHTML = "Erase";
                    break;

                case "delete_date":
                    pid = data_el.getAttribute('pid');
                    date = data_el.getAttribute('date');

                    // set data for action if carried out
                    CLU.modal.data = {
                        "pid": pid,
                        "date": date
                    };

                    // display modal text
                    mtitle.innerHTML = "Confirm date delete";
                    mbody.innerHTML = 'Confirm you wish to delete the date <b class="accent3">' + date + '</b> and any clutcher associated with it.';
                    maction_button.innerHTML = "Delete";
                    break;

                case "delete_poll":
                    pid = data_el.getAttribute('pid');
                    ptitle = data_el.getAttribute('poll_title');

                    CLU.modal.data = {
                        "pid": pid
                    }

                    // display modal text
                    mtitle.innerHTML = "Confirm poll delete";
                    mbody.innerHTML = '<p>Confirm you wish to delete the poll titled:<br>' +
                        '<b class="accent3">' + ptitle + '</b></p>' +
                        '<p>Note that all details, dates, and clutchers will be <b class="accent2">permanently</b> deleted!<br>In life, sometimes, there is no going back.<br><span class="accent3">This will be one of those times.</span></p>'
                    maction_button.innerHTML = "Destroy poll";
                    break;

                default:
                    mtitle.innerHTML = "Modal title";
                    mbody.innerHTML = "Modal body";
                    maction_button.innerHTML = "Confirm";
            }
        });
    },
    processModal: function(event) {
        switch (CLU.modal.action) {
            case "delete_clutcher":
                CLU.modifyPoll(CLU.modal.action, CLU.modal.data);
                break;

            case "delete_date":
                CLU.modifyPoll(CLU.modal.action, CLU.modal.data);
                break;

            case "delete_poll":
                CLU.modifyPoll(CLU.modal.action, CLU.modal.data);
                break;

            default:
                console.log("Failed to determine resolve action:" + CLU.modal.action);
        }
    },
    copyUrl: function(evt) {
        navigator.clipboard.writeText(evt.target.getAttribute('data')).then(() => {
          /* clipboard successfully set */
        }, () => {
          /* clipboard write failed */
            console.log("failed");
        });
    },
    modifyPoll: function(action, data) {
        fetch("/api/" + action, {
            method: 'POST',
            cache: 'no-cache',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then((response) => response.json())
        .then((resp) => {
            if ( resp === "success" ) {
                switch(action) {
                    case 'delete_date':
                        CLU.deleteElement('date_' + data.date);
                        break;

                    case 'delete_clutcher':
                        CLU.deleteElement('clutcher_' + data.date);
                        break;

                    case 'add_date':
                        location.reload();
                        break;

                    case 'delete_poll':
                        CLU.deleteElement('pid_' + data.pid);
                        break;

                    default:
                        console.log("Unexpected action success response.");
                }
            } else {
                // make Toast to warn of failure - do it later
            }
        });
    },
    deleteElement: function (eid) {
        const delem = document.getElementById(eid);
        delem.parentElement.removeChild(delem);
    },
    controller: new AbortController(),
    searchValidEmail: function (el) {
        let email = el.value;
        let subbut = document.getElementById("emailTransferSubmit");

        subbut.innerHTML = 'Not valid';

        if (this.validateEmail(email)) {

            // indicate that we're checking if the email is valid
            subbut.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';

            // See if we made an earlier and ongoing request
            if (!this.controller.signal.aborted) {
                // cancel any previous request if it exists
                this.controller.abort();
                this.controller = new AbortController();
            }

            const signal = this.controller.signal;

            // check if this is in the DB
            fetch("/api/email_validation", {
                method: 'POST',
                cache: 'no-cache',
                signal: signal,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({'email': email})
            })
            .then((response) => response.json())
            .then((resp) => {
                if ( resp === "success" ) {
                    subbut.innerHTML = 'Transfer poll ownership';
                    subbut.disabled = false;
                } else {
                    subbut.innerHTML = 'Not found';
                    subbut.disabled = true;
                }
            })
            .catch(e => {
                // do nothing, further typing cancelled the fetch
            });
        } else {
            subbut.disabled = true;
        };
    },
    validateEmail: function (email) {
        return String(email)
            .toLowerCase()
            .match(
              /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    }
};

CLU.init();

