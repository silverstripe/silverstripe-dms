<% if $getDocumentSets %>
    <div class="documentsets">
        <% loop $getDocumentSets %>
            <% include DocumentSet %>
        <% end_loop %>
    </div>
<% end_if %>
