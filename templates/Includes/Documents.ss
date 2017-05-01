<% if PageDocuments %>
    <div class="documents">
        <h3><%t DMSDocument.PLURALNAME "Documents" %></h3>
        <% loop PageDocuments %>
            <% include Document %>
        <% end_loop %>
    </div>
<% end_if %>
