<% if $DocumentSets %>
    <div class="documentsets">
        <% loop $DocumentSets %>
            <% include DocumentSet %>
        <% end_loop %>
    </div>
<% end_if %>
