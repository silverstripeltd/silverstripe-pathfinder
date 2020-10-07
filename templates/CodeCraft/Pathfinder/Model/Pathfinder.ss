<h2>$Title</h2>
$StartContent
<% if $Questions %>
    <% if $HasProgress %>
        <a href="$RestartLink" title="$StartButtonText">$StartButtonText</a>
        <a href="$ProgressLink" title="$ContinueButtonText">$ContinueButtonText</a>
    <% else %>
        <a href="$StartLink" title="$StartButtonText">$StartButtonText</a>
    <% end_if %>
<% else %>
    <p class="message error">Sorry, no questions have been added to this Pathfinder yet.</p>
<% end_if %>
