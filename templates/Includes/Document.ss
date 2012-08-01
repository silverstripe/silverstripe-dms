<div class="document $FileExt">
	<% if Title %>
		<h4><a href="$DownloadLink" title="Download $Title">$Title</a></h4>
	<% else %>
		<h4><a href="$DownloadLink" title="Download $FilenameWithoutID">$FilenameWithoutID</a></h4>
	<% end_if %>
	
	<p class='details'>
		<strong>$FilenameWithoutID</strong>
		| $FileExt
		| $FileSizeFormatted
		| Last Changed: $LastChanged.Nice
	</p>
	<% if Description %>
		<p>$Description</p>
	<% end_if %>
</div>