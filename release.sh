#!/bin/bash
set -e

VERSION_FILE=".version"

check_version () {
	version="$1"
	if ! [[ "${version}" =~ ^v[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]
	then
	    echo -e "\\nThat version does not match semver"
	    return 1
	else
		return 0
	fi
}

confirm_branch () {
	current_branch=$(git rev-parse --abbrev-ref HEAD)
	read -rp "Do you want to tag the current branch (${current_branch})? <y/N> " prompt
	if ! [[ "${prompt}" =~ [yY](es)* ]]
	then
		echo "Checkout the correct branch and retry. Aborting..."
		exit
	fi
}

get_hash () {
	current_hash=$(git rev-parse HEAD)
}

get_new_version () {
	# Get new version to release
	read -rp "What version do you want to release? " new_version
	while ! check_version "$new_version"; do
	    read -rp "New version: " new_version
	done
	check_version "${new_version}"
}

get_prev_version () {
	# Get previous version to generate release notes
	read -rp "What previous version should be used to generate release notes?" prev_version
	while ! check_version "$prev_version"; do
	    read -rp "New version: " prev_version
	done
}

print_release_notes () {
	# Print release notes
	echo -e "\\nRelease notes: "
	release_notes=$(git --no-pager log "${prev_version}"..."${current_hash}" --no-merges --oneline)
	echo -e "${release_notes}"
}

confirm_tagging () {
	# prompt to continue
	read -rp "Are you sure you want to update the version and tag? <y/N> " prompt
	if ! [[ "${prompt}" =~ [yY](es)* ]]
	then
		echo "Aborting..."
		exit
	fi
}

update_version () {
	# update and commit local version file used by tracking telemetry
	echo -e "\\nWriting version file..."
	echo "${new_version}" > "${VERSION_FILE}"
	
	echo -e "\\nCommitting version file..."
	git add "${VERSION_FILE}"
	git commit -m "[RELEASE] ${new_version}"
	git push origin "${current_branch}"
}

push_tag () {
	# push version to github
	echo -e "\\nTagging..."
	git tag "${new_version}" -m "[RELEASE] ${new_version}" -m "${release_notes}"
	git push origin tag "${new_version}"
}

handle_package_manager () {
	# script or instructions to push to package manager
	echo -e "\\nYou should now publish this version to the appropriate package manager"
}

confirm_branch
get_hash
get_new_version
get_prev_version
print_release_notes
confirm_tagging
update_version
push_tag
handle_package_manager
