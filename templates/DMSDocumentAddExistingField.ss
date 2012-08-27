<div class="ss-add">
	<div class="document-add-existing <% if useFieldContext %>field<% else %>link-editor-context<% end_if %>">

		<h3>
			<span class="step-label">
				<% if useFieldContext %>
				<span class="flyout">1</span><span class="arrow"></span>
				<% else %>
				<span class="flyout">3</span><span class="arrow"></span>
				<% end_if %>
				<span class="title">Link a Document</span>
			</span>
		</h3>

		<input class="document-autocomplete text" type="text" placeholder="Search by ID or filename" />
		<!-- <span>or Add from page</span> -->
		$fieldByName(PageSelector)

		<div class="document-list">

		</div>

	</div>

	<div class="ss-assetuploadfield">
		<h3>
			<span class="step-label">
				<% if useFieldContext %>
				<span class="flyout">2</span><span class="arrow"></span>
				<span class="title">Edit Document Details</span>
				<% else %>
				<span class="flyout">4</span><span class="arrow"></span>
				<span class="title">Selected Document</span>
				<% end_if %>

			</span>
		</h3>
		<!-- <div class="fileOverview"></div> -->
		<ul class="files ss-uploadfield-files ss-add-files"></ul>
	</div>
</div>