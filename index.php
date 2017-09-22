<?php

include_once('functions.php');

// POST content validation.
if( isset($_POST) && ! empty($_POST) )
{
  // Increment usage statistics.
  usage_stat();

	// Get POST content.
	$article = $_POST['article'];
	$references = str_replace('<p><strong>Given references:</strong></p>', '', $_POST['references']);

	// Remove highlights existed.
	$article = str_replace( array('<mark>', '</mark>'), '', $article);

	// Screening for content within brackets
	$re = '/(?<=\()(.*?)(?=\))/';
	preg_match_all($re, $article, $in_brackets);

  print_r($in_brackets); //debug

	// Separate content between semicolons into fragments
	foreach($in_brackets[0] as $content)
	{
		// No semicolon found.
		if( count( explode(';', $content) ) == 1 )
		{
			$fragments[] = $content;

		// Semicolon found. Fragments content.
		} else {
			foreach(explode(';', $content) as $in_smcolon)
			{
				$fragments[] = $in_smcolon;
			}
		}
	}

	if( ! empty($fragments) )
	{
		$counter = 0;

		// Purify fragments for citations.
		foreach($fragments as $fragment)
		{
			// Remove whitespaces.
			$fragment = trim($fragment);

			// Find fragments with format (YYYY) or (YYYYa).
			if( ! strpos($fragment, ',') )
			{
				if( strlen($fragment) == 5 && ctype_alnum($fragment) || strlen($fragment) == 4 && ctype_digit($fragment) )
				{
					// Check if fragment within desired range.
					preg_match_all('/\d+/', $fragment, $digit);

					if($digit[0] >= 1700 && $digit[0] <= date("Y"))
					{
						$citations[] = $fragment;
						$counter = $counter + 1;
					}
				}

			// Find fragments with format (AB C, YYYY).
			} else {
				$portion = explode(',', $fragment);

				$author = trim($portion[0]);
				$year = trim($portion[1]);

				if( strlen($year) == 5 && ctype_alnum($year) || strlen($year) == 4 && ctype_digit($year) )
				{
					// Check if year within desired range.
					preg_match('/\d+/', $year, $digit);
					if($digit[0] >= 1700 && $digit[0] <= date("Y"))
					{
						// Check if author valid.
						preg_match('/\d+/', $author, $num_in_author);
						preg_match('/\w+/', $author, $alpha_in_author);
						if( ! empty($alpha_in_author) && empty($num_in_author) )
						{
							$citations[] = $fragment;
							$counter = $counter + 1;
						}
					}
				}
			}
		}
	}

	if( ! empty($citations) )
	{
		// Highlight citations found.
    		$re = '~\\b(' . implode('|', $citations) . ')\\b~';
    		$article = preg_replace($re, '<mark>$0</mark>', $article);

		// Generate reference list.
		asort($citations);
		$citation_list = array_unique($citations);
    }
}
?>
<html>
<head>
  <!---Meta--->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Citector is an academic tool that helps to detect and highlight all possible in-text citations, so that the proofreading process could be more convenient. It has two modes of action that can be used in combination or independent: auto-detection (Search for possible citations base_url()d on prediction) and reverse-detection (Search for citations base_url()d on reference list).">
  <meta name="keywords" content="academic;tool;in-text;citation;essay;research;writing;proffreading;citector;citation;detector">

  <!---Stylesheets--->
  <!---Bootstrap--->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css">
  <!---Front Awesome--->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <!---Summernote Editor--->
  <link rel="stylesheet" href="<?php echo base_url('assets/Summernote/summernote-bs4.css'); ?>">
  <!---Quill Editor--->
  <link rel="stylesheet" href="<?php echo base_url('assets/site/css/quill.bubble.css'); ?>">

  <!---Global stylesheet--->
  <link rel="stylesheet" href="<?php echo base_url('assets/site/css/style.css'); ?>">

  <style>
  @media screen and (min-width: 400px) {
    textarea {
      min-height: 55vh;
    }
    aside {
      margin-top: 80px;
    }
  }
  @media screen and (min-width: 576px) {
    textarea {
      min-height: 55vh;
    }
    aside {
      margin-top: 70px;
    }
  }

  @media screen and (min-width: 768px) {
    textarea {
      min-height: 55vh;
    }
    aside {
      margin: auto;
    }
  }

  @media screen and (min-width: 992px) {
    textarea {
      min-height: 60vh;
    }
    aside {
      margin: auto;
    }
  }

  @media (min-width: 1200px) {
    textarea {
      min-height: 69vh;
    }
    aside {
      margin: auto;
    }
  }
  </style>

</head>
<body>
  <!---Header--->
  <header class="container-fluid px-4" style="position: relative">
    <div class="row text-center">
      <!---Logo--->
      <section class="col-lg-2 col-md-3 col-sm-5 col-xs-12">
        <img class="img-fluid w-100" src="<?php echo base_url(); ?>assets/site/img/logo.png" style="margin-top: 15; max-width: 250px">
      </section><!---END Logo--->
      <!---Notification--->
      <section class="col-lg-6 col-md-4 hidden-sm-down text-left">
        <small class="text-danger" style="position: absolute; bottom: 0">Current supported format: APA referencing style</small>
      </section><!---END Notification--->
      <!---Actions--->
      <section class="col-lg-4 col-md-4 col-sm-5 col-xs-12">
        <div class="btn-group" style="margin: 25px 0 5px 0">
          <button class="btn btn-sm btn-link text-danger" type="submit" style="text-decoration: none" data-toggle="modal" data-target="#advOptions">Advanced options <i class="fa fa-caret-down fa-fw"></i> </button>
          <button class="btn btn-success" type="submit" form="content">Show my citations <i class="fa fa-search fa-fw"></i> </button>
        </div>
      </section><!---END Actions--->
    </div>
  </header><!---END Header--->

  <!---Form action--->
  <form id="content" method="POST" action="index.php"></form>
  <input type="text" form="content" id="input-references" name="references" style="display: none">

  <!---Main--->
  <main class="container-fluid py-2 bg-faded">
    <div class="row">
      <!---Content--->
      <section class="col-md-8 h-75">
        <textarea class="form-control w-100" id="article" name="article" form="content" style="border: none; resize: none" placeholder="Insert your article here"><?php echo isset($article) ? $article : ''; ?></textarea>
      </section><!---END Content--->

      <!---Asides--->
      <aside class="col-md-4 h-75 py-1">
        <div class="row">
          <!---References--->
          <section class="col-md-12 h-50">
            <div id="references" class="w-100 h-100" style="background: #fff; border-bottom: dashed 1px #ccc"><?php echo isset($references) && ! empty($references) ? '<p><strong>Given references:</strong></p>' . $references : ''; ?></div>
	         </section><!---END References--->
           <!---Citation list--->
           <section class="col-md-12 h-50">
             <div id="citation-list" class="w-100 h-100" style="background: <?php echo isset($citation_list) && ! empty($citation_list) ? '#fff' : '#E9E9E9'; ?>">
               <?php
                 $i = 0;
                 if( isset($citation_list) ) :

                   echo '<b>Detected references (' . count($citation_list) . '):</b>';

                   foreach($citation_list as $citation) :
                     echo '<br>' . ++$i . '. ' . $citation;
                   endforeach;

                 endif;
               ?>
            </div>
          </section><!---END Citation list--->
        </div>
      </aside><!---END Asides--->
    </div>
  </main><!---END Main--->

  <!-- Footer -->
  <footer class="container-fluid py-1">
    <div class="row">
      <!---Counters--->
      <section class="col-md-6 col-sm-12 text-left">
        <left class="hidden-md-down">
          <small class="text-muted">
	      <?php echo isset($counter) ? $counter . ($counter > 1 ? ' citations found.' : ' citation found.') : '0 citation found.'; ?>
	      <?php echo ! empty($stat) ? $stat . ' performs previously.' : '0 perform previously.'; ?>
          </small>
        </left>
        <center class="hidden-md-up">
          <small class="text-muted">
	      <?php echo isset($counter) ? $counter . ($counter > 1 ? ' citations found.' : ' citation found.') : '0 citation found.'; ?>
	      <?php echo ! empty($stat) ? $stat . ' performs previously.' : '0 perform previously.'; ?>
          </small>
        </center>
      </section><!---END Counters--->
      <!---Copyright information--->
      <section class="col-md-6 col-sm-12 text-right">
        <right class="hidden-md-down">
          <small class="text-muted">
            <em>Developed by <a href="http://www.jvloo.com/about" class="text-danger">JVLOO</a> &copy; 2017 All rights reserved.</em>
           (<a href="http://www.jvloo.com/citector/change-log.html" class="text-danger">Version 2.0</a>)
          </small>
        </right>
        <center class="hidden-md-up">
          <small class="text-muted">
            <em>Developed by <a href="http://www.jvloo.com/about" class="text-danger">JVLOO</a> &copy; 2017 All rights reserved.</em><br>
           (<a href="http://www.jvloo.com/citector/change-log.html" class="text-danger">Version 2.0</a>)
          </small>
        </center>
      </section><!---END Copyright information--->
    </div>
  </footer><!---END Footer--->

  <!---Modal(Advanced options)--->
  <div id="advOptions" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">X</button>
          <h4 class="modal-title">Modal Header</h4>
        </div>
        <div class="modal-body">
          <p>Some text in the modal.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div><!---END Modal--->


  <!---Javacripts--->
  <!---Bootstrap essentials --->
  <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>

  <!---Summernote Editor--->
  <script src="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.8/summernote-bs4.js"></script>
  <script>
    $(document).ready(function() {
      $('#article').summernote({
        height: 300,                 // set editor height
        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor
        focus: true,                 // set focus to editable area after initializing summernote
        placeholder: 'Insert your aticle here.'
      });
    });
  </script>

  <!---Quill Editor--->
  <script src="https://cdn.quilljs.com/1.3.2/quill.min.js"></script>
  <script>
    var toolbarOptions = [
      ['bold', 'italic', 'underline', 'strike'],
      [{ 'color': [] }, { 'background': [] }],
      [{ 'align': [] }],
      [{ 'indent': '-1'}, { 'indent': '+1' }],
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    ];

    var references = new Quill('#references', {
      modules: {
        toolbar: toolbarOptions
      },
      placeholder: 'Paste your references here(optional)',
      theme: 'bubble'
    });

    <?php if( isset($citation_list) && ! empty($citation_list) ) : ?>
      var citationList = new Quill('#citation-list', {
        modules: {
          toolbar: toolbarOptions
        },
        theme: 'bubble'
      });
    <?php endif; ?>

    var form = document.querySelector('form');

    form.onsubmit = function() {
      var inputReferences = references.root.innerHTML;
	     if(inputReferences !== '<p><br></p>') {
            $('#input-references').val(inputReferences);
       }
    };
  </script>
</body>
</html>
