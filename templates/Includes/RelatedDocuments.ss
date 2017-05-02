<h5><%t DMSDocument.RELATED_DOCUMENTS "Related documents" %></h5>

<ul class="documents-relateddocuments">
    <% loop $getRelatedDocuments %>
        <li>
            <% if $Title %>
                <a href="$Link" title="<%t DMSDocument.DOWNLOAD "Download {title}" title=$Title %>">$Title</a>
            <% else %>
                <a href="$Link" title="<%t DMSDocument.DOWNLOAD "Download {title}" title=$FilenameWithoutID %>">$FilenameWithoutID</a>
            <% end_if %>
            <span class="documents-relateddocuments-documentdetails"><% include DocumentDetails %></span>
        </li>
    <% end_loop %>
</ul>
