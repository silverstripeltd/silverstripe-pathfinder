<div class="results">
    <% if $Results %>
        $ResultsFoundContent

        <ul class="results-list">
            <% loop $Results %>
                <li>
                    <a href="$Link" title="$Title">$MenuTitle</a>
                </li>
            <% end_loop %>
        </ul>
    <% else %>
        $ResultsNotFoundContent
    <% end_if %>
</div>
<div class="support">
    $SupportContent
</div>
