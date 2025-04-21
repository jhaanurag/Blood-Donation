<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once 'api_handler.php';
include_once '../includes/header.php';

// Initialize result variables
$eligibility_result = '';
$eligibility_details = '';
$eligibility_class = '';

// Process eligibility form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;
    $weight = isset($_POST['weight']) ? (int)$_POST['weight'] : 0;
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $health_conditions = isset($_POST['health_conditions']) ? $_POST['health_conditions'] : [];
    $medications = isset($_POST['medications']) ? $_POST['medications'] : [];
    $recent_travel = isset($_POST['recent_travel']) ? $_POST['recent_travel'] : 'no';
    $last_donation = isset($_POST['last_donation']) ? $_POST['last_donation'] : 'never';
    $tattoo_piercing = isset($_POST['tattoo_piercing']) ? $_POST['tattoo_piercing'] : 'no';
    $pregnancy = isset($_POST['pregnancy']) ? $_POST['pregnancy'] : 'no';
    $surgery = isset($_POST['surgery']) ? $_POST['surgery'] : 'no';
    
    // Basic eligibility checks
    $issues = [];
    $warnings = [];
    
    // Age check
    if ($age < 17) {
        $issues[] = "You must be at least 17 years old to donate blood.";
    } elseif ($age > 65) {
        $warnings[] = "Donors over 65 may need additional health screening.";
    }
    
    // Weight check
    if ($weight < 110) { // 110 pounds = 50 kg (approximate)
        $issues[] = "You must weigh at least 110 pounds (50 kg) to donate blood.";
    }
    
    // Health conditions check
    $deferring_conditions = [
        'diabetes_uncontrolled' => 'Uncontrolled diabetes may affect eligibility.',
        'heart_disease' => 'Heart conditions may affect eligibility.',
        'cancer' => 'Cancer diagnosis may affect eligibility depending on treatment status.',
        'blood_disorder' => 'Blood disorders may affect eligibility.',
        'hepatitis' => 'Hepatitis history may permanently defer donation.',
        'hiv' => 'HIV positive status permanently defers donation.',
        'recent_infection' => 'Recent infections may temporarily defer donation.',
        'low_iron' => 'Low iron levels may temporarily defer donation.'
    ];
    
    foreach ($health_conditions as $condition) {
        if (isset($deferring_conditions[$condition])) {
            $issues[] = $deferring_conditions[$condition];
        }
    }
    
    // Medication check
    $deferring_medications = [
        'blood_thinners' => 'Blood thinners may affect eligibility.',
        'accutane' => 'Accutane (isotretinoin) typically requires a waiting period after stopping.',
        'antibiotics' => 'Recent antibiotics may require temporary deferral.',
        'finasteride' => 'Finasteride typically requires a waiting period.',
        'immunosuppressants' => 'Immunosuppressant medications may affect eligibility.'
    ];
    
    foreach ($medications as $medication) {
        if (isset($deferring_medications[$medication])) {
            $issues[] = $deferring_medications[$medication];
        }
    }
    
    // Travel check
    if ($recent_travel === 'yes') {
        $warnings[] = "Recent travel to certain areas may affect eligibility. The blood center will need specific details.";
    }
    
    // Last donation check
    if ($last_donation === 'less_than_8_weeks') {
        $issues[] = "You should wait at least 8 weeks between whole blood donations.";
    } elseif ($last_donation === 'less_than_16_weeks') {
        $warnings[] = "Make sure it has been at least 8 weeks since your last donation.";
    }
    
    // Tattoo/piercing check
    if ($tattoo_piercing === 'less_than_3_months') {
        $issues[] = "Recent tattoos or piercings may require a waiting period of 3-12 months depending on local regulations.";
    } elseif ($tattoo_piercing === 'less_than_12_months') {
        $warnings[] = "Some blood centers require a waiting period after tattoos or piercings.";
    }
    
    // Pregnancy check (only for females)
    if ($gender === 'female' && $pregnancy === 'currently') {
        $issues[] = "You cannot donate while pregnant.";
    } elseif ($gender === 'female' && $pregnancy === 'less_than_6_weeks') {
        $issues[] = "You should wait at least 6 weeks after giving birth before donating blood.";
    }
    
    // Recent surgery check
    if ($surgery === 'less_than_6_months') {
        $issues[] = "You may need to wait until fully recovered from surgery before donating.";
    }
    
    // Determine eligibility result
    if (count($issues) > 0) {
        $eligibility_result = 'Not Eligible';
        $eligibility_details = "<p class='font-semibold mb-2'>Based on your responses, you may not be eligible to donate blood at this time due to:</p><ul class='list-disc pl-6'>";
        foreach ($issues as $issue) {
            $eligibility_details .= "<li>$issue</li>";
        }
        $eligibility_details .= "</ul>";
        $eligibility_class = 'text-red-600 bg-red-100 dark:bg-red-900 dark:text-red-200';
        
        if (count($warnings) > 0) {
            $eligibility_details .= "<p class='font-semibold mt-4 mb-2'>Additional considerations:</p><ul class='list-disc pl-6'>";
            foreach ($warnings as $warning) {
                $eligibility_details .= "<li>$warning</li>";
            }
            $eligibility_details .= "</ul>";
        }
    } elseif (count($warnings) > 0) {
        $eligibility_result = 'Potentially Eligible';
        $eligibility_details = "<p class='font-semibold mb-2'>You may be eligible to donate, but there are some considerations:</p><ul class='list-disc pl-6'>";
        foreach ($warnings as $warning) {
            $eligibility_details .= "<li>$warning</li>";
        }
        $eligibility_details .= "</ul>";
        $eligibility_class = 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-200';
    } else {
        $eligibility_result = 'Likely Eligible';
        $eligibility_details = "<p>Based on your responses, you appear to meet the basic eligibility criteria for blood donation.</p><p class='mt-2'>Remember that this is just a preliminary assessment. The final eligibility determination will be made by healthcare professionals at the donation site.</p>";
        $eligibility_class = 'text-green-600 bg-green-100 dark:bg-green-900 dark:text-green-200';
    }
    
    // Add disclaimer
    $eligibility_details .= "<p class='mt-4 text-sm italic'>This is not medical advice. The final eligibility determination will be made by healthcare professionals at the donation center based on a complete health screening.</p>";
}
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <h1 class="text-3xl font-bold text-red-700 mb-6">Donor Eligibility Predictor</h1>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Check Your Blood Donation Eligibility</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Answer the following questions to get a preliminary assessment of your eligibility to donate blood. This tool provides general guidance only and does not replace professional medical screening.</p>
        
        <?php if (!empty($eligibility_result)): ?>
            <!-- Eligibility Result -->
            <div class="rounded-lg p-6 mb-8 <?= $eligibility_class ?>">
                <h3 class="text-xl font-bold mb-3">Result: <?= $eligibility_result ?></h3>
                <div><?= $eligibility_details ?></div>
            </div>
        <?php endif; ?>
        
        <form method="post" action="" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="age" class="block text-gray-700 dark:text-gray-300 mb-2">Age</label>
                    <input 
                        type="number" 
                        id="age" 
                        name="age" 
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                        required 
                        min="16" 
                        max="100"
                    >
                </div>
                
                <div>
                    <label for="weight" class="block text-gray-700 dark:text-gray-300 mb-2">Weight (pounds)</label>
                    <input 
                        type="number" 
                        id="weight" 
                        name="weight" 
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                        required 
                        min="50" 
                        max="500"
                    >
                </div>
                
                <div>
                    <label for="gender" class="block text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                    <select 
                        id="gender" 
                        name="gender" 
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                        required
                    >
                        <option value="">Select...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div>
                    <label for="last_donation" class="block text-gray-700 dark:text-gray-300 mb-2">Last Blood Donation</label>
                    <select 
                        id="last_donation" 
                        name="last_donation" 
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                        required
                    >
                        <option value="never">Never donated</option>
                        <option value="less_than_8_weeks">Less than 8 weeks ago</option>
                        <option value="less_than_16_weeks">8-16 weeks ago</option>
                        <option value="more_than_16_weeks">More than 16 weeks ago</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-3">Health Conditions (select all that apply)</label>
                <div class="grid md:grid-cols-2 gap-2">
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="diabetes_controlled" class="rounded text-red-600">
                            <span class="ml-2">Controlled Diabetes</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="diabetes_uncontrolled" class="rounded text-red-600">
                            <span class="ml-2">Uncontrolled Diabetes</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="heart_disease" class="rounded text-red-600">
                            <span class="ml-2">Heart Disease</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="hypertension_controlled" class="rounded text-red-600">
                            <span class="ml-2">Controlled Hypertension</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="hypertension_uncontrolled" class="rounded text-red-600">
                            <span class="ml-2">Uncontrolled Hypertension</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="cancer" class="rounded text-red-600">
                            <span class="ml-2">Cancer (current or past)</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="blood_disorder" class="rounded text-red-600">
                            <span class="ml-2">Blood Disorder</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="hepatitis" class="rounded text-red-600">
                            <span class="ml-2">Hepatitis</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="hiv" class="rounded text-red-600">
                            <span class="ml-2">HIV/AIDS</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="recent_infection" class="rounded text-red-600">
                            <span class="ml-2">Recent Infection</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="low_iron" class="rounded text-red-600">
                            <span class="ml-2">Low Iron/Anemia</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="health_conditions[]" value="thyroid" class="rounded text-red-600">
                            <span class="ml-2">Thyroid Condition</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-3">Current Medications (select all that apply)</label>
                <div class="grid md:grid-cols-2 gap-2">
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="medications[]" value="blood_pressure" class="rounded text-red-600">
                            <span class="ml-2">Blood Pressure Medication</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="medications[]" value="blood_thinners" class="rounded text-red-600">
                            <span class="ml-2">Blood Thinners</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="medications[]" value="accutane" class="rounded text-red-600">
                            <span class="ml-2">Accutane/Isotretinoin</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="medications[]" value="antibiotics" class="rounded text-red-600">
                            <span class="ml-2">Antibiotics</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="medications[]" value="finasteride" class="rounded text-red-600">
                            <span class="ml-2">Finasteride/Propecia</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="medications[]" value="immunosuppressants" class="rounded text-red-600">
                            <span class="ml-2">Immunosuppressants</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="medications[]" value="aspirin" class="rounded text-red-600">
                            <span class="ml-2">Aspirin/NSAIDs</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="medications[]" value="insulin" class="rounded text-red-600">
                            <span class="ml-2">Insulin</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="recent_travel" class="block text-gray-700 dark:text-gray-300 mb-2">Recent International Travel (last 3 months)</label>
                    <select 
                        id="recent_travel" 
                        name="recent_travel" 
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                        required
                    >
                        <option value="no">No</option>
                        <option value="yes">Yes</option>
                    </select>
                </div>
                
                <div>
                    <label for="tattoo_piercing" class="block text-gray-700 dark:text-gray-300 mb-2">Recent Tattoo or Piercing</label>
                    <select 
                        id="tattoo_piercing" 
                        name="tattoo_piercing" 
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                        required
                    >
                        <option value="no">No</option>
                        <option value="less_than_3_months">Yes, within last 3 months</option>
                        <option value="less_than_12_months">Yes, within last 4-12 months</option>
                        <option value="more_than_12_months">Yes, more than 12 months ago</option>
                    </select>
                </div>
                
                <div>
                    <label for="pregnancy" class="block text-gray-700 dark:text-gray-300 mb-2">Pregnancy Status (if applicable)</label>
                    <select 
                        id="pregnancy" 
                        name="pregnancy" 
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                        <option value="no">Not applicable</option>
                        <option value="currently">Currently pregnant</option>
                        <option value="less_than_6_weeks">Gave birth less than 6 weeks ago</option>
                        <option value="more_than_6_weeks">Gave birth more than 6 weeks ago</option>
                    </select>
                </div>
                
                <div>
                    <label for="surgery" class="block text-gray-700 dark:text-gray-300 mb-2">Recent Surgery</label>
                    <select 
                        id="surgery" 
                        name="surgery" 
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                        required
                    >
                        <option value="no">No</option>
                        <option value="less_than_6_months">Yes, within last 6 months</option>
                        <option value="more_than_6_months">Yes, more than 6 months ago</option>
                    </select>
                </div>
            </div>
            
            <div class="text-center">
                <button 
                    type="submit" 
                    class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg transition duration-200"
                >
                    Check Eligibility
                </button>
                
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    This tool provides general guidance only. The final eligibility determination will be made by healthcare professionals at the donation site.
                </p>
            </div>
        </form>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Common Eligibility Requirements</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-medium text-lg text-gray-700 dark:text-gray-200">Age</h3>
                        <p class="text-gray-600 dark:text-gray-300">Most donors must be at least 17 years old. Some states allow 16-year-olds to donate with parental consent.</p>
                    </div>
                    <div>
                        <h3 class="font-medium text-lg text-gray-700 dark:text-gray-200">Weight</h3>
                        <p class="text-gray-600 dark:text-gray-300">Donors must weigh at least 110 pounds (approximately 50 kg).</p>
                    </div>
                    <div>
                        <h3 class="font-medium text-lg text-gray-700 dark:text-gray-200">Overall Health</h3>
                        <p class="text-gray-600 dark:text-gray-300">You should be feeling well and healthy on the day of donation. No active infections or illnesses.</p>
                    </div>
                    <div>
                        <h3 class="font-medium text-lg text-gray-700 dark:text-gray-200">Waiting Periods</h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            - At least 8 weeks (56 days) between whole blood donations<br>
                            - At least 16 weeks (112 days) between double red cell donations<br>
                            - At least 7 days between platelet donations<br>
                            - At least 3 days between plasma donations
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="md:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Have Questions?</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">If you have specific questions about your eligibility to donate blood, you can:</p>
                <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                    <li>• Ask our <a href="index.php" class="text-red-600 hover:text-red-800">Blood Donation Assistant</a></li>
                    <li>• Contact your local blood donation center</li>
                    <li>• Call the national blood donor helpline</li>
                </ul>
                <div class="mt-6">
                    <a href="index.php" class="inline-block bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        Chat with Assistant
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>