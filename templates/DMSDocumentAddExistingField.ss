<div class="ss-add field treedropdown searchable dmsdocument-addexisting">
    <div class="document-add-existing <% if $useFieldContext %>field<% else %>link-editor-context<% end_if %>">
    <% if $useFieldContext %><h3><% else %><div><% end_if %>
        <span class="step-label">
            <% if $useFieldContext %>
                <span class="flyout">1</span><span class="arrow"></span>
                <strong class="title"><%t DMSDocumentAddExistingField.LINKADOCUMENT "Link a Document" %></strong>
            <% end_if %>
        </span>
    <% if $useFieldContext %></h3><% else %></div><% end_if %>

    <% if not $useFieldContext %>
        <label><%t DMSDocumentAddExistingField.LINKADOCUMENT "Link a Document" %></label>
        <div class="middleColumn">
    <% end_if %>
            <input class="document-autocomplete text" type="text" placeholder="<%t DMSDocumentAddExistingField.AUTOCOMPLETE "Search by ID or filename" %>" />
            $fieldByName(PageSelector)
            <div class="document-list"></div>
    <% if not $useFieldContext %>
        </div>
    <% end_if %>
    </div>

    <div class="ss-assetuploadfield <% if useFieldContext %>field<% else %>link-editor-context<% end_if %>">
        <div class="step4">
            <span class="step-label">
                <% if useFieldContext %>
                    <span class="flyout">2</span><span class="arrow"></span>
                    <strong class="title"><%t DMSDocumentAddExistingField.EDITDOCUMENTDETAILS "Edit Document Details" %></strong>
                <% else %>
                    <label><%t DMSDocumentAddExistingField.SELECTED "Selected Document" %></label>
                <% end_if %>
            </span>
        </div>
        <% if not $useFieldContext %>
            <div class="middleColumn">
        <% end_if %>
            <ul class="files ss-uploadfield-files ss-add-files"></ul>
        <% if not $useFieldContext %>
            </div>
        <% end_if %>
    </div>
</div>
