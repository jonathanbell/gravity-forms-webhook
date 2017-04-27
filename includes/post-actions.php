<?php

function gfprwh_post_to_remote($entry, $form) {

  $pr_field_count = get_option('gfprwh_count');
  $educ_post_urls = array();
  
  for ($i = 1; $i <= $pr_field_count; $i++) {

    if (get_option('gfprwh_formID__'.$i) == $form['id']) {

      $url = get_option('gfprwh_form_post_url__'.$i);

      // Convert any nested objects to arrays.
      $form = json_decode(json_encode($form), true);

      $i = 0;
      $data_to_send = array();

      // Construct a sensible array ($data_to_send) to send to our listener over the Internet. 
      // Its format will be: 'Field_Label' => 'field_value' (spaces replaced by '_')
      foreach ($form['fields'] as $key => $value) {
        $data_to_send[$form['fields'][$i]['label']] = (isset($entry[$i + 1]) ? $entry[$i + 1] : '');
        $i++;
      }

      // "Url-ify" the data for the POST request.
      $fields_string = http_build_query($data_to_send);

      // HTTP POST the cURL way.
      // $ch = curl_init();

      // Set the url, number of POST vars, POST data, etc.
      // curl_setopt($ch, CURLOPT_URL, $url);
      // curl_setopt($ch, CURLOPT_POST, 1);
      // curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

      // Send POST.
      // $response = curl_exec($ch);

      // Close connection.
      // curl_close($ch);
      
      // HTTP POST the Wordpress way.
      if (!class_exists('WP_Http')) {
        include_once(ABSPATH.WPINC.'/class-http.php');
      }
      
      $request = new WP_Http();
      $response = $request->post($url, array('body' => $fields_string, 'sslverify' => false));

      // Write a debug file if we are on a development machine.
      if ($_SERVER['HTTP_HOST'] == 'localhost:8000') {
        file_put_contents(__DIR__.'/educ-gf-post-receive.debug', '$data_to_send:'.PHP_EOL.print_r($data_to_send, true).PHP_EOL.'$fields_string:'.PHP_EOL.$fields_string.PHP_EOL.PHP_EOL.'$response:'.PHP_EOL.print_r($response, true));
      }

    } // if get_option('gfprwh_formID__'.$i)

  } // for $i <= $pr_field_count; 

} // gfprwh_post_to_remote()
