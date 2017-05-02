<% if $isHidden != true %>
    <div class="document $Extension">
        <% if $Title %>
            <h4><a href="$Link" title="<%t DMSDocument.DOWNLOAD "Download {title}" title=$Title %>">$Title</a></h4>
        <% else %>
            <h4><a href="$Link" title="<%t DMSDocument.DOWNLOAD "Download {title}" title=$FilenameWithoutID %>">$FilenameWithoutID</a></h4>
        <% end_if %>

        <p class="details"><% include DocumentDetails %></p>
        <% if $Description %>
            <p>$DescriptionWithLineBreak</p>
        <% end_if %>

        <% if $getRelatedDocuments %>
            <% include RelatedDocuments %>
        <% end_if %>
    </div>
<% end_if %>
