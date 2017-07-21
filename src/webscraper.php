<?php
$url='http://archive-grbj-2.s3-website-us-west-1.amazonaws.com/';
$html = file_get_contents($url); //get the html returned from the following url
$pokemon_doc = new DOMDocument();

libxml_use_internal_errors(TRUE); //disable libxml errors

if(!empty($html)){ //if any html is actually returned

    $pokemon_doc->loadHTML($html);
    libxml_clear_errors(); //remove errors for yucky html
    $pokemon_xpath = new DOMXPath($pokemon_doc);

    //get all the h2's with an id
    $pokemon_row = $pokemon_xpath->query('//a[@*]');
    //query('a[@class="url"]', $pat)->item(0)->nodeValue;
    $linkArray = array();
    if($pokemon_row->length > 0){
        foreach($pokemon_row as $row){
            $_link = $row->getAttribute('href');
            if (strpos($_link, 'articles/') !== false) {
                if(!in_array($_link, $linkArray)){
                        $linkArray[] = $_link;
                }
            }
        }
    }

    if(count($linkArray) > 0){
        $i=0;
        $data=array();
        foreach ($linkArray as $key => $value) {
            $link = $url.$value;
            $detail = file_get_contents($link);
            if(!empty($detail)){
                preg_match_all('/<h1>(.*?)<\/h1>/s', $detail, $title);
                $articleTitle=$title[1][0];

                preg_match_all('/<div class="meta clearfix">(.*?)<\/div>/s', $detail, $metaData);
                preg_match_all('/<div class="date">(.*?)<\/div>/s', $metaData[0][0], $metaDate);
                $ArticleDate=$metaDate[1][0];

                preg_match_all('/<div class="author">(.*?)<\/div>/s', $detail, $authorBio);
                preg_match_all('/<a href="(.*?)">(.*?)<\/a>/s', $authorBio[0][0], $authorB);
                $authorUrl=str_replace("../","",$authorB[1][0]);
                $authorName=str_replace("../","",$authorB[2][0]);

                $authorLink = $url.$authorUrl;
                $authBio=file_get_contents($authorLink);
                if(!empty($authBio)){

                    preg_match_all('/<div class="author-info">(.*?)<\/div>/s', $authBio, $bio);
                    preg_match_all('/<div class="abstract">(.*?)<\/div>/s',$bio[0][0], $authAbs);
                    $bios=implode(' ', $authAbs[1][0]);
                }
                
                $data[$i]['authorName'] = $authorName;
                $data[$i]['articles'] = array(
                    "articleTitle" => $articleTitle,
                    "articleUrl" => $link,
                    "articledate" => $ArticleDate
                    );
                $data[$i]['authorUrl'] = $authorUrl;
                $data[$i]['authorBio'] = $bios;

            }
            
            $i++;
        }
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}
?>