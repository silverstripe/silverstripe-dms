<div class="document $File.ClassByExtension <% if ClassName = DocumentTextLabel %>documentLabel<% end_if %>">
	<a class="full-link" <% if ClassName = Document %>href="$Link"<% end_if %> title="Download $File.Name"></a>
<% if Title %>
	<h4><a href="$Link" title="Download $File.Name">$Title</a></h4>
<% else %>
	<h4><a href="$Link" title="Download $File.Name">$File.Name</a></h4>
<% end_if %>
	<% if ClassName = Document %>
		<p class='details'>
			<strong>$File.Name</strong>
			| $File.Extension
			| $File.Size
			| Modified: <% control File.Document %> $LastEdited.Nice <% end_control %>
		</p>
		<% if Description %>
			<p>$Description</p>
		<% end_if %>
	<% end_if %>
	<% if ClassName = DocumentTextLabel %>
		<p class='details'>
			<% if Description %>
				<strong>$Description</strong>
			<% end_if %>
		</p>
	<% end_if %>
</div>