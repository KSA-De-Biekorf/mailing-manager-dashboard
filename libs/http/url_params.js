function set_param(url_str, param_c, val_c) {
	const url = new URL(url_str);
	let new_url = "";
	new_url += url.protocol;
	new_url += url.hostname;
	new_url += url.pathname;
	let paramIdx = 0;
	let param_c_set = false;
	for (let [param, val] of url.searchParams) {
		let divider; 
		if (paramIdx == 0) {
			divider = "?";
		} else {
			divider = "&";
		}

		let new_val = val;
		if (param == param_c) {
			param_c_set = true;
			new_val = val_c;
		}
		new_url += `${divider}${param}=${new_val}`;
		
		paramIdx += 1;
	}
	if (!param_c_set) {
		let divider;
		if (paramIdx == 0) divider = "?";
		else divider = "&";
		new_url += `${divider}${param_c}=${val_c}`;
	}

	new_url += url.hash;

	return new_url;
}
