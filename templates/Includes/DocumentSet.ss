<% if $getDocuments %>
    <div class="documentsets-set">
        <% if $Title %>
            <h3>$Title</h3>
        <% end_if %>

        <% loop $getDocuments.Sort(DocumentSort) %>
            <% include Document %>
        <% end_loop %>
    </div>
<% end_if %>
