<% if not $isHidden %>
    <div class="document $Extension">
        <h4><a href="$Link" title="<%t DMSDocument.DOWNLOAD "Download {title}" title=$getTitle %>">$getTitle</a></h4>

        <% if $CoverImage %>
            <div class="article-thumbnail">
                $CoverImage.FitMax(100, 100)
            </div>
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
