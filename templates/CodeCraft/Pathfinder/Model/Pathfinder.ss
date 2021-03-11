<h2>$Title</h2>
$StartContent
<% if $Questions %>
    <a href="$StartLink" title="$StartButtonText">$StartButtonText</a>
    <% if $HasProgress %>
        <a href="$ProgressLink" title="$ContinueButtonText">$ContinueButtonText</a>
    <% end_if %>
<% else %>
    <p class="message error">
        <%t CodeCraft\Pathfinder\Model\Pathfinder.NO_QUESTIONS_TEXT "Sorry, no questions have been added to this Pathfinder yet." %>
    </p>
<% end_if %>
