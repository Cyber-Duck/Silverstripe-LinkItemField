<div class="ss-link-item-modal">
    <div class="modal-backdrop fade show"></div>
    <div data-reactroot role="dialog">
        <div role="dialog" class="insert-link__dialog-wrapper--internal fade show modal" style="display: block; padding-left: 15px;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Link Item</h4>
                        <button type="button" class="close" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal__dialog modal-body">
                        <% if $IncludeFormTag %>
                        <form $AttributesHTML>
                        <% end_if %>
                            <% if $Message %>
                            <p id="{$FormName}_error" class="message $MessageType">$Message</p>
                            <% else %>
                            <p id="{$FormName}_error" class="message $MessageType" style="display: none"></p>
                            <% end_if %>

                            <fieldset>
                                <% if $Legend %><legend>$Legend</legend><% end_if %>
                                <% loop $Fields %>
                                    $FieldHolder
                                <% end_loop %>
                                <div class="clear"><!-- --></div>
                            </fieldset>

                            <% if $Actions %>
                            <div class="btn-toolbar">
                                <% loop $Actions %>
                                    $Field
                                <% end_loop %>
                            </div>
                            <% end_if %>
                        <% if $IncludeFormTag %>
                        </form>
                        <% end_if %>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>