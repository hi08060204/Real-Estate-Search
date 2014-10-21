<!DOCTYPE html>
<html lang="en">
  <head>
     <style>
        body{
           text-align: center;
        }
        table {
            text-align: left;
        }     
        table.submitForm {
            padding: 1%;
            border: 3px solid black;
            width: 37%;
        }

        table.resultTable {
            width: 90%;
        }     

       table.resultTable tr.head td { 
            border: 1px solid black;
            background-color: #FEFFCD;
        }
        table.resultTable th:last-child { 
            padding-left: 5%;
        }    
     </style>
     <script>
      
        function checkEmpty() {
            var blank = "";
            var address_filled = city_filled = state_filled = false;
            if (document.send.address.value != blank) address_filled = true;  
            if (document.send.city.value != blank) city_filled = true;  
            if (document.send.state.value != blank) state_filled = true;  
            
            if (address_filled && city_filled && state_filled){
                document.getElementById('nonEmpty').value ="nonEmpty"; 
                return true;
            }
           
            var alertMsg = "Please enter value for ";
            if (!address_filled) alertMsg += "Street Address";
            if (!city_filled) {
                alertMsg += (!address_filled)? " and City": "City";
            }  
            if (!state_filled){
                alertMsg += (!address_filled || !city_filled)? " and State": "State";
            }
            alertMsg += ".";  
            alert(alertMsg);
            return false;
        }
        </script>
      </head>
      <body>
        <h2>Real Estate Search</h2>
        <form name="send" method="post" action="">
            <table class='submitForm' align='center'>
              <tr><td>Street Address*:</td>
              <td><input name="address" type="text" value="<?php echo isset($_POST['address'])?$_POST['address']:""?>"></td><tr>
              <tr><td>City*:</td>
              <td><input name="city" type="text" value="<?php echo isset($_POST['city'])?$_POST['city']:""?>"></td>
             
              <tr><td>State*:</td><td><select id='sel' name='state' value="">
              <?php
                    error_reporting(0);
                    $options = array("", "AL","AK","AZ","AR","CA","CO","CT","CO","CT","DE","DC",
                                "FL","GA","HI","ID","IL","IN","IA","KS","KY","LA","ME",
                                "MD","MA","MI","MN","MS","MO","MT","NE","NV","NH","NJ",
                                "NM","NY","NC","ND","OH","OK","OR","PA","RI","SC","SD",
                                "TN","TX","UT","VT","VA","WA","WV","WI","WY" );

                    foreach($options as $opt){
                        echo "<option value='".$opt."' "; 
                        if ($opt==$_POST['state']){
                            echo " selected ";
                        }
                        echo ">".$opt."</option>"; 
                    }
              ?>
              </select></td></tr>
              <input type="hidden" id="nonEmpty" name="nonEmpty">
              <tr><td></td><td><input style="vertical-align: top;" type="submit" id="submit" 
                                      name="submit" value="Search" onclick="checkEmpty()">
              <img  src="http://www.zillow.com/widgets/GetVersionedResource.htm?path=/static/logos/Zillowlogo_150x40_rounded.gif" 
                    width="150" height="40" alt="Zillow Real Estate Search" />
              </td></tr>
              <tr colspan='2'><td><i>* - Mandatory fields.</i></td></tr>
            </table>
       </form>
        <?php 
            error_reporting(0);
            $prefix = "http://www.zillow.com/webservice/GetDeepSearchResults.htm?";
            $ziwId = "X1-ZWz1dxqbbokmx7_1pt0f";

            if(isset($_POST["submit"]) &&  $_POST["nonEmpty"] != ""){   // Generate URL for retrieving XML file from Zillow
                $address = preg_replace("/ /","+", $_POST["address"]);
                $city = preg_replace("/ /","+", $_POST["city"]);
                
                $url = $prefix."zws-id=".$ziwId."&address=".$address."&citystatezip=".$city."%2C+"
                       .$_POST["state"]."&rentzestimate=true";  
                ParseXML($url);
                
            }
                
            function ParseXML($url) {
                $searchresults = simplexml_load_file($url);
                
                if ($searchresults->message->code != 0){
                //The case if returned XML is NOT O.K. !    
                echo "No exact match found -- Verify that the given address is correct";
                return false;
                }
                $results = $searchresults->response->results; 
            
            
                $downArrow = "http://cs-server.usc.edu:45678/hw/hw6/down_r.gif";
                $upArrow = "http://cs-server.usc.edu:45678/hw/hw6/up_g.gif";
                $termOfUse = "http://www.zillow.com/corp/Terms.htm";
                $zillowPage = "http://www.zillow.com/zestimate/";

            
                setlocale(LC_MONETARY, 'en_US');
                echo "<h2>Search Results</h2>";
                echo "<table class='resultTable' align='center'>";
                foreach($results->children() as $result) {
                    echo "<tr class='head'><td colspan='4'>See more details for   <a href='".$result->links->homedetails."'>".
                    $result->address->street.", ".$result->address->city.", ".$result->address->state." ".
                    $result->address->zipcode."</a> on Zillow</td></tr>";
           
                    echo "<tr><th>Property Type:</th><td>".$result->useCode."</td>";
                    echo "<th>Last Sold Price:</th><td align='right'>".money_format('%n', (float)$result->lastSoldPrice).
                    "</td></tr>";
                    echo "<tr><th>Year Built:</th><td>".$result->yearBuilt."</td>";
                
                    $lastSoldDate = DateTime::createFromFormat('m/d/Y',$result->lastSoldDate);
                
                    echo "<th>Last Sold Date:</th><td align='right'>".$lastSoldDate->format('d-M-Y')."</td></tr>";
                    echo "<tr><th>Lot Size:</th><td>".$result->lotSizeSqFt." sq. ft.</td>";
                
                    $zLastUpdateDate = DateTime::createFromFormat('m/d/Y',$result->zestimate->{'last-updated'});
                    echo "<th>Zestimate<sup>&#174;</sup> Property Estimate as of ".$zLastUpdateDate->format('d-M-Y')."</th>";
                    echo "<td align='right'>".money_format('%n',(float)$result->zestimate->amount)."</td></tr>";
            
                    echo "<tr><th>Finished Area:</th><td>".$result->finishedSqFt." sq. ft.</td>";
                    echo "<th>30 Days Overall Change";
                    if ((float)$result->zestimate->valueChange > 0.0) {
                        echo "<img src='".$upArrow."'/>";
                    } else {
                        echo "<img src='".$downArrow."'/>";
                    }
                    echo ":</th><td align='right'>".money_format('%n',abs((float)$result->zestimate->valueChange))."</td></tr>";
                
                    echo "<tr><th>Bathrooms:</th><td>".$result->bathrooms."</td>";
                    echo "<th>All Time Property Range:</th><td align='right'>".
                    money_format('%n', (float)$result->zestimate->valuationRange->low)." - ".
                    money_format('%n', (float)$result->zestimate->valuationRange->high)."</td></tr>";
                     
                    echo "<tr><th>Bedrooms:</th><td>".$result->bedrooms."</td>";
                    $rentLastUpdateDate = DateTime::createFromFormat('m/d/Y',$result->rentzestimate->{'last-updated'});
                    echo "<th>Rent Zestimate<sup>&#174;</sup> Rent Valuation as of ".$rentLastUpdateDate->format('d-M-Y').
                    "</th>";
                    echo "<td align='right'>".money_format('%n', (float)$result->rentzestimate->amount)."</td></tr>";
                
                    echo "<tr><th>Tax Assessment Year:</th><td>".$result->taxAssessmentYear."</td>";
                    echo "<th>30 Days Rent Change";
                    if ((float)$result->rentzestimate->valueChange > 0.0){
                        echo "<img src='".$upArrow."'/>";    
                    } else {
                        echo "<img src='".$downArrow."'/>";
                    }
                
                    echo ":</th><td align='right'>".money_format('%n', abs((float)$result->rentzestimate->valueChange)).
                    "</td></tr>";
                
                    echo "<tr><th>Tax Assessment:</th><td>".money_format('%n', (float)$result->taxAssessment)."</td>";
                    echo "<th>All Time Rent Change:</th><td align='right'>".
                    money_format('%n', (float)$result->rentzestimate->valuationRange->low)." - ".
                    money_format('%n', (float)$result->rentzestimate->valuationRange->high)."</td></tr><br>";
                    
                }
                echo "</table><br>";

                echo" <div>&#169; Zillow, Inc., 2006-2014. Use is subject to "; 
                echo "<a href='".$termOfUse."'>Terms of Use</a><br>"; 
                echo "<a href='".$zillowPage."' >What's a Zestimate?</a><div>";

            }
    ?> 
  <body>
</html>
