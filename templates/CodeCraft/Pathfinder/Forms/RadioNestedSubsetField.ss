<div $setAttribute('data-role', 'nesting-parent').AttributesHTML>
    <input
            id="Field_{$ID}"
            class="radio<% if $Children %> radio-with-children<% end_if %>"
            name="$Name"
            type="radio"
            value="$Value.ATT"
            data-role="parent-input"
            <% if $isDisabled %>disabled="disabled"<% end_if %>
            <% if $isChecked %>checked="checked"<% end_if %>
            <% if $Children %>style="display: none;"<% end_if %>
    />
    <label for="Field_{$ID}" class="radionestedsubset-label<% if $Children %> radionestedsubset-label-with-children<% end_if %>">
        <% if $Children %>
            <% loop $Children %>
                $FieldHolder
            <% end_loop %>
        <% else %>
            $Title
        <% end_if %>
    </label>
</div>
