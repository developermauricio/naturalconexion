jQuery(document).ready(function ($) {
    'use strict';

    class Messenger {
        constructor() {
            this.index = 0;
            let table = $('#wacv-messenger-root table');

            this.tableBody = table.find('tbody');

            $('.wacv-add-row').on('click', this.addRow.bind(this));

            table.on('click', '.wacv-remove-row', this.removeRow);

            this.loadSavedData();
        }

        renderRow(item = {}) {
            let {hearing = '', reply = ''} = item;
            this.index++;
            return `<tr>
                        <td><input type="text" class="wacv-hearing-sample" name="wacv_messages[${this.index}][hearing]" value="${hearing}"></td>
                        <td><input type="text" class="wacv-reply-message" name="wacv_messages[${this.index}][reply]" value="${reply}"></td>
                        <td><i class="icon trash wacv-remove-row"> </i></td>
                    </tr>`;
        }

        addRow() {
            let row = this.renderRow();
            this.tableBody.append(row);
        }

        removeRow() {
            $(this).closest('tr').remove();
        }

        loadSavedData() {
            if (Object.keys(wacvParams.data).length) {
                for (let i in wacvParams.data) {
                    let item = wacvParams.data[i];
                    let row = this.renderRow(item);
                    this.tableBody.append(row);
                }
            }
        }
    }

    new Messenger();
});