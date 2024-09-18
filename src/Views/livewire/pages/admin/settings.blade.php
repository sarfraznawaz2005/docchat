<div>

    <x-doctalk::admin.sidebarlinks/>

    <div class="page">

        <x-doctalk::errors/>

        <fieldset>
            <legend>Delete Old Conversations (Except Pinned)</legend>

            <div class="form-container">
                <small>
                    This will automatically delete older conversations (except pinned by users)
                    after specified number of days. Use 0 as value to disable this feature.
                </small>
                <br>

                <div class="form-group">
                    <label for="delete-conversations">Number of Days</label>
                    <input
                        type="number"
                        wire:model="days"
                        id="delete-conversations"
                        name="delete-conversations"
                        placeholder="Enter number of days"
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; transition: border-color 0.3s ease;"
                    >
                </div>

                <br>
                <div class="form-group" style="margin-bottom: 0;">
                    <button
                        wire:click="saveSettings"
                        class="btn btn-green">

                        <x-doctalk::icons.ok width="20" height="20"/>
                        <span style="margin-bottom:2px; margin-left: 5px;">Save Settings</span>
                    </button>
                </div>
            </div>
        </fieldset>

        <fieldset style="margin-top: 50px !important;">
            <legend>Delete Everything</legend>

            <div class="form-container">

                <small>
                    This will delete all document, user conversations and their messages effectively emptying up
                    all chat data. This action is irreversible.
                </small>
                <br>

                <x-doctalk::confirm-dialog call="deleteAll"
                                           text="Are you sure you want to delete all data? This action is irreversible.">
                    <div class="btn btn-red flex text-center alignCenter justifyCenter" style="width: 250px;">
                        <x-doctalk::icons.delete/>
                        <span style="margin-left: 5px;">Delete All Data</span>
                    </div>
                </x-doctalk::confirm-dialog>

            </div>

        </fieldset>

    </div>

</div>
