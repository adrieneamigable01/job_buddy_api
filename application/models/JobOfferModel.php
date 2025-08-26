<?php
/**
 * Job Offer Model
 * Author: Adriene Carre Amigable
 * Date Created : 3/12/2025
 * Version: 0.0.1
 */
class JobOfferModel extends CI_Model {

    /**
     * Add a new job offer to the database.
     * 
     * @param array $payload Data to insert into the job_offer table.
     * @return string Returns the compiled insert query.
     */
    public function add($payload) {
        // Ensure all required fields are included in the payload.
        return $this->db->set($payload)->get_compiled_insert('job_offers');
    }

    /**
     * Update an existing job offer in the database.
     * 
     * @param array $payload Data to update in the job_offer table.
     * @param array $where Condition to identify the job offer to update.
     * @return string Returns the compiled update query.
     */
    public function update($payload, $where) {
        // Ensure the fields to be updated are in the payload.
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('job_offers');
    }

    /**
     * Retrieve job offers from the database based on the given conditions.
     * 
     * @param array $payload Conditions to filter the job offers.
     * @return array Returns the result of the query.
     */
    public function get($payload,$resultType = null) {
        $this->db->select('
            job_offers.job_offers_id,
            job_offers.job_title,
            job_offers.skills,
            job_offers.location,
            job_offers.min_salary,
            job_offers.max_salary,
            employer.user_id,
            CONCAT(job_offers.min_salary," - ",job_offers.max_salary) as salary_range,
            job_offers.employer_id,
            job_offers.job_description,
            job_offers.company_id,
            company.company_name,
            job_offers.employment_type,
            job_offers.date_added,
            job_offers.expired_at,
            job_offers.is_active,
            job_offers.status,
            job_offers.company_overview,
            job_offers.qualifications,
            job_offers.work_start,
            job_offers.work_end,
        ');
        $this->db->from('job_offers');
        $this->db->join('company', 'job_offers.company_id = company.company_id', 'left'); // Left join to fetch admin details
        $this->db->join('employer', 'employer.employer_id  = job_offers.employer_id', 'left'); // Left join to fetch admin details
        $this->db->where($payload);
        $query = $this->db->get();
        if($resultType == "array"){
            return $query->result_array();
        }else{
            return $query->result();
        }
        
     
    }

    public function getJobOfferById($job_offer_id)
    {
        return $this->db
                ->select('job_offers.*,  company.company_id,
                company.company_name,
                company.company_address,
                company.contact_number as company_contact_number,
                company.email as company_email,
                company.established_date') // select job offer fields + company name
                ->from('job_offers')
                ->join('company', 'company.company_id = job_offers.company_id', 'left')
                ->where('job_offers.job_offers_id', $job_offer_id)
                ->where('job_offers.deleted_at', null)
                ->get()
                ->row();
    }

    public function getMatchingCandidatesOld($criteria) {
        $this->db->select('
            students.student_id,
            students.lastname,
            students.firstname,
            students.middlename,
            students.user_id,
            students.email,
            students.phone,
            students.address,
            students.birthdate,
            students.gender,
            students.is_active,
            students.created_at,
            students.updated_at,
            students.deleted_at,
            students.skills,
            students.prefere_available_start_time,
            students.prefere_available_end_time,
            students.employment_type,
            students.course_id,
            courses.courses
        ');
        $this->db->from('students')
                 ->join('courses', 'students.course_id = courses.course_id', 'left');
    
        // Only filter active students
        if (isset($criteria['is_active'])) {
            $this->db->where('students.is_active', $criteria['is_active']);
        }
    
        $query = $this->db->get();
        $candidates = $query->result_array();
    
        // Apply AI matching
        return $this->rankCandidatesByAI($candidates, $criteria);
    }
    


    private function rankCandidatesByAI($candidates, $criteria) {
        $requiredSkills = isset($criteria['skills']) ? array_map('strtolower', $criteria['skills']) : [];
        $requiredEmploymentType = strtolower($criteria['employment_type'] ?? '');
        $requiredStartTime = strtolower($criteria['prefere_available_start_time'] ?? '');
        $requiredEndTime = strtolower($criteria['prefere_available_end_time'] ?? '');
        $requiredCourseId = $criteria['course_id'] ?? null;
    
        foreach ($candidates as &$candidate) {
            $score = 0;
    
            // Skills (50%)
            $candidateSkills = explode(',', strtolower(!empty($candidate['skills_array']) > 0 ? $candidate['skills_array'] : ""));
            $skillMatches = array_intersect($candidateSkills, $requiredSkills);
            $skillMatchRatio = count($requiredSkills) > 0 ? count($skillMatches) / count($requiredSkills) : 0;
            $score += $skillMatchRatio * 50;
    
            // Employment type (30%)
            if (strtolower($candidate['employment_type']) === $requiredEmploymentType) {
                $score += 30;
            }
    
            // Preferred available time (10%)
            if (strtolower($candidate['prefere_available_time']) === $requiredTime) {
                $score += 10;
            }
    
            // Course ID (10%)
            if ($requiredCourseId !== null && $candidate['course_id'] == $requiredCourseId) {
                $score += 10;
            }
    
            $candidate['match_score'] = round($score, 2); // Round for cleaner output
        }
    
        // Sort candidates by match_score descending
        usort($candidates, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });
    
        return $candidates;
    }


    
    public function getMatchingCandidates($criteria)
    {
     
        $apiKey = 'ku4pOcnw7HIGqQkdDCxYYx5OCCULrjH041yny4ne'; // Replace with your Cohere API key

        // Step 1: Fetch students
        $this->db->select('
            students.student_id,
            students.lastname,
            students.firstname,
            students.middlename,
            students.user_id,
            students.email,
            students.phone,
            students.address,
            students.birthdate,
            students.gender,
            students.is_active,
            students.created_at,
            students.updated_at,
            students.deleted_at,
            students.skills,
            students.prefere_available_start_time,
            students.prefere_available_end_time,
            students.employment_type,
            students.course_id,
            courses.courses,
            COUNT(DISTINCT experience.experience_id) as experience_count
        ');
        $this->db->from('students')
            ->join('courses', 'students.course_id = courses.course_id', 'left')
            ->join('experience', 'experience.student_id = students.student_id', 'left')
            ->join('education', 'education.student_id = students.student_id', 'left')
            ->where('students.is_active', 1)
            ->group_by('students.student_id');

        if (isset($criteria['students.is_active'])) {
            $this->db->where('students.is_active', $criteria['students.is_active']);
        }

        $query = $this->db->get();
        $candidates = $query->result_array();
      
        foreach ($candidates as &$candidate) {
            // Normalize skills
          
            if (is_string($candidate['skills'])) {
                $decoded = json_decode($candidate['skills'], true);
                if (is_array($decoded)) {
                    $candidate['skills_array'] = $decoded;
                } else {
                    // Fallback: treat as comma-separated string
                    $candidate['skills_array'] = array_map('trim', explode(',', $candidate['skills']));
                }
            } else {
                // Already an array
                $candidate['skills_array'] = $candidate['skills'];
            }
            $candidate['employment_history'] = [];

            // Simulate past job experiences and skills used
            for ($i = 0; $i < (int)$candidate['experience_count']; $i++) {
                $candidate['employment_history'][] = [
                    'position' => 'Job ' . ($i + 1),
                    'skills_used' => ['PHP', 'JavaScript'], // Simulated, ideally pulled from DB
                    'duration_months' => 6
                ];
            }

            // ---- Skill Match Scoring ----
            $requiredSkills = isset($criteria['students.skills']) 
            ? array_map('trim', explode(',', $criteria['students.skills'])) 
            : [];
            $candidateSkills = $candidate['skills_array'] ?? [];
  
            $matchedSkills = array_intersect($requiredSkills, $candidateSkills);

            $candidate['related_skills_score'] = count($requiredSkills) > 0 ? round((count($matchedSkills) / count($requiredSkills)) * 100, 2) : 0;
        
            // ---- Experience Match Scoring ----
            $experienceScore = 0;
            foreach ($candidate['employment_history'] as $job) {
                $jobSkills = $job['skills_used'] ?? [];
                if (array_intersect($requiredSkills, $jobSkills)) {
                    $experienceScore++;
                }
            }
            $candidate['related_experience_score'] = $experienceScore * 10; // Adjust scaling as needed
           
            // ---- Location Proximity Scoring ----
            $candidate['location_proximity_score'] = $this->getLocationProximityScore(
                strtolower($criteria['students.location'] ?? ''),
                strtolower($candidate['address'] ?? '')
            );
        }
        

        return $this->rankCandidatesWithCohere($candidates, $criteria, $apiKey);
    }

    private function getLocationProximityScore($jobLocation, $candidateAddress)
    {
        // Check if both job and candidate locations are exactly the same
        if (strpos($candidateAddress, $jobLocation) !== false) {
            return 10; // Same location
        }
        
        // Call Google Maps API to get distance
        $distance = $this->getDistanceFromGoogle($jobLocation, $candidateAddress);
    
        // Check if the distance is valid and less than 15km
        if ($distance !== false && $distance < 15) {
            return 5; // Nearby (within 15km)
        }
    
        // If distance is more than 15km, return 0 (far)
        return 0; // Far
    }


    private function getDistanceFromGoogle($origin, $destination)
    {
        // Your Google Maps API Key
        $apiKey = 'AIzaSyANgvotjUcej5YzdY30IDLk0pKLfghtnxc';
        
        // Encode the addresses to be URL-safe
        $origin = urlencode($origin);
        $destination = urlencode($destination);
        
        // Google Maps Distance Matrix API URL
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destination}&key={$apiKey}";
        
        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // Execute cURL request and get the response
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Decode the JSON response
        $data = json_decode($response, true);
        
        // Check if the response contains valid data
        if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
            // Distance in meters, convert to kilometers
            $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value'];
            $distanceInKilometers = $distanceInMeters / 1000;
            return $distanceInKilometers;
        }
        
        return false; // Return false if the response is invalid
    }

    private function rankCandidatesWithCohere($candidates, $criteria, $apiKey)
    {
        $prompt = "You are an AI recruiter. Score candidates from 0 to 100 based on these weights:

        - Practical Job Experience (past projects, certifications): 17%
        - Skills Match (skills + skills from past jobs): 18%
        - Course Compatibility (related coursework/field): 20%
        - Time Availability & Distance (schedule + location): 45%

        Time Availability rules:
        - If job is FULL-TIME, candidate must fully cover job work_start → work_end.
        - If job is PART-TIME, candidate availability must overlap with job schedule.

        Details (from database):
        - `practical_job_experience_score`: prior work experience relevancy
        - `skills_score`: how well candidate’s skills match
        - `course_compatibility_score`: course relevance
        - `time_and_distance_score`: free time + proximity combined
        - Candidate has fields: `prefere_available_start_time`, `prefere_available_end_time`
        - Job has fields: `work_start`, `work_end`, `employment_type`

        Return JSON like:
        [
        {
            \"student_id\": 1,
            \"match_score\": 88,
            \"reason\": \"Good skills match, relevant course, availability overlaps, same city.\"
        }
        ]

        Only include candidates with a score of 50 or higher. 

        Job Criteria:
        " . json_encode($criteria, JSON_PRETTY_PRINT) . "

        Candidates:
        " . json_encode($candidates, JSON_PRETTY_PRINT);

        $postData = [
            'model' => 'command-r-plus',
            'prompt' => $prompt,
            'max_tokens' => 1200,
            'temperature' => 0.4
        ];

        $ch = curl_init('https://api.cohere.ai/v1/generate');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($postData)
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception("Cohere API error: " . curl_error($ch));
        }

        $responseData = json_decode($response, true);
        $text = $responseData['generations'][0]['text'] ?? '';
        preg_match('/\[\s*{.*}\s*\]/s', $text, $matches);
        $json = $matches[0] ?? '[]';

        $rankedResults = json_decode($json, true);

        // Step: merge candidate details back
        $rankedWithDetails = [];
        foreach ($rankedResults as $ranked) {
            if ($ranked['match_score'] >= 50) {
                foreach ($candidates as $candidate) {
                    if ($candidate['student_id'] == $ranked['student_id']) {
                        // Add extra flag for availability check
                        $isAvailable = $this->checkAvailability(
                            $criteria['work_start'] ?? null,
                            $criteria['work_end'] ?? null,
                            $candidate['prefere_available_start_time'] ?? null,
                            $candidate['prefere_available_end_time'] ?? null,
                            $criteria['students.employment_type'] ?? null
                        );

                        $rankedWithDetails[] = array_merge($candidate, [
                            'match_score'   => $ranked['match_score'],
                            'match_reason'  => $ranked['reason'],
                            'is_available'  => $isAvailable
                        ]);
                        break;
                    }
                }
            }
        }

        return $rankedWithDetails;
    }

    /**
     * Check availability with employment type logic
     */
    private function checkAvailability($jobStart, $jobEnd, $candStart, $candEnd, $employmentType)
    {
        if (!$jobStart || !$jobEnd || !$candStart || !$candEnd) {
            return false;
        }

        $jobStartTime = strtotime($jobStart);
        $jobEndTime   = strtotime($jobEnd);
        $candStart    = strtotime($candStart);
        $candEnd      = strtotime($candEnd);

        if (strtolower($employmentType) === 'fulltime') {
            // Must fully cover
            return ($candStart <= $jobStartTime && $candEnd >= $jobEndTime);
        } else {
            // Part-time → overlap allowed
            return ($candStart < $jobEndTime && $candEnd > $jobStartTime);
        }
    }

    
    public function rankJobOffersWithCohere($student, $jobOffers, $apiKey)
    {
        $prompt = "You are an AI job matchmaker. Score job offers for a student from 0 to 100 based on these weights:

    - Skills Match: 25%
    - Course Match: 20%
    - Availability Match: 25%
    - Employment Type Match: 20%
    - Location Proximity: 10%

    Definitions:
    - `skills_match_score`: how well the student's skills match the job requirements
    - `course_match_score`: match between student's course and preferred course for the job
    - `availability_match_score`: does student's preferred time match job's availability
    - `employment_type_match_score`: full-time/part-time preference match
    - `location_proximity_score`: 0 (far), 5 (nearby), 10 (same city)

    Return JSON like:
    [
    {
        \"job_offer_id\": 12,
        \"match_score\": 85,
        \"reason\": \"Strong skill match, correct course, full-time match, and nearby location.\"
    }
    ]

    Only include job offers with score 50 or higher.

    Student Profile:
    " . json_encode($student, JSON_PRETTY_PRINT) . "

    Job Offers:
    " . json_encode($jobOffers, JSON_PRETTY_PRINT);

        $postData = [
            'model' => 'command-r-plus',
            'prompt' => $prompt,
            'max_tokens' => 1000,
            'temperature' => 0.4
        ];

        $ch = curl_init('https://api.cohere.ai/v1/generate');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($postData)
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception("Cohere API error: " . curl_error($ch));
        }

        $responseData = json_decode($response, true);
        $text = $responseData['generations'][0]['text'] ?? '';
        preg_match('/\[\s*{.*}\s*\]/s', $text, $matches);
        $json = $matches[0] ?? '[]';

        $rankedResults = json_decode($json, true);

        $rankedWithDetails = [];
        foreach ($rankedResults as $ranked) {
            if ($ranked['match_score'] >= 50) {
                foreach ($jobOffers as $job) {
                    if ($job['job_offers_id'] == $ranked['job_offer_id']) {
                        $rankedWithDetails[] = array_merge($job, [
                            'match_score' => $ranked['match_score'],
                            'match_reason' => $ranked['reason']
                        ]);
                        break;
                    }
                }
            }
            if (count($rankedWithDetails) >= 10) {
                break;
            }
        }

        return $rankedWithDetails;
    }

    public function updateByStudentAndOffer($student_id, $job_offer_id, $payload) {
        $this->db->where('student_id', $student_id);
        $this->db->where('job_offer_id', $job_offer_id);
        return $this->db->update('student_job_offers', $payload);
    }

    /**
     * Check if a job offer already exists based on job title, employer ID, and company ID.
     * 
     * @param string $job_title The job title.
     * @param int $employer_id The employer ID.
     * @param int $company_id The company ID.
     * @param int $exclude_id The job offer ID to exclude from the check.
     * @return bool Returns true if the job offer exists, otherwise false.
     */
    public function isJobOfferExists($job_title, $employer_id, $company_id, $exclude_id = "") {
        try {
            $this->db->select('id');
            $this->db->from('job_offers');
            $this->db->where('job_title', $job_title);
            $this->db->where('employer_id', $employer_id);
            $this->db->where('company_id', $company_id);

            // Exclude the job offer ID if it's provided.
            if (!empty($exclude_id)) {
                $this->db->where('id !=', $exclude_id);
            }

            $query = $this->db->get();

            // Check if there are any rows returned.
            return ($query->num_rows() > 0);
        } catch (Exception $e) {
            // Log the error or handle it as needed.
            log_message('error', 'Error in isJobOfferExists method: ' . $e->getMessage());
            return false;  // Return false in case of an error.
        }
    }

    public function getAllActiveCandidates()
    {
        $this->db->select('
            students.student_id,
            students.lastname,
            students.firstname,
            students.middlename,
            students.user_id,
            students.email,
            students.phone,
            students.address,
            students.birthdate,
            students.gender,
            students.is_active,
            students.created_at,
            students.updated_at,
            students.deleted_at,
            students.skills,
            students.prefere_available_start_time,
            students.prefere_available_end_time,
            students.employment_type,
            students.course_id,
            courses.courses,
            COUNT(DISTINCT experience.experience_id) as experience_count
        ');
        $this->db->from('students')
            ->join('courses', 'students.course_id = courses.course_id', 'left')
            ->join('experience', 'experience.student_id = students.student_id', 'left')
            ->join('education', 'education.student_id = students.student_id', 'left')
            ->where('students.is_active', 1)
            ->group_by('students.student_id');

        $query = $this->db->get();
        $candidates = $query->result_array();

        // Normalize and enrich candidate data
        foreach ($candidates as &$candidate) {
            // Normalize skills
            if (is_string($candidate['skills'])) {
                $decoded = json_decode($candidate['skills'], true);
                if (is_array($decoded)) {
                    $candidate['skills_array'] = $decoded;
                } else {
                    $candidate['skills_array'] = array_map('trim', explode(',', $candidate['skills']));
                }
            } else {
                $candidate['skills_array'] = $candidate['skills'];
            }

            // Simulate job history
            $candidate['employment_history'] = [];
            for ($i = 0; $i < (int)$candidate['experience_count']; $i++) {
                $candidate['employment_history'][] = [
                    'position' => 'Job ' . ($i + 1),
                    'skills_used' => ['PHP', 'JavaScript'], // Simulated
                    'duration_months' => 6
                ];
            }
        }

        return $candidates;
    }
    // public function getRankedCandidatesForJobOffer($jobCriteria, $allCandidates)
    // {
    //     $apiKey = 'ku4pOcnw7HIGqQkdDCxYYx5OCCULrjH041yny4ne'; // Replace with real key

    //     $rankedCandidates = [];

    //     foreach ($allCandidates as $candidate) {
    //         $requiredSkills = isset($jobCriteria['students.skills']) 
    //             ? array_map('trim', explode(',', $jobCriteria['students.skills'])) 
    //             : [];

    //         $candidateSkills = $candidate['skills_array'] ?? [];
    //         $matchedSkills = array_intersect($requiredSkills, $candidateSkills);

    //         $candidate['related_skills_score'] = count($requiredSkills) > 0 
    //             ? round((count($matchedSkills) / count($requiredSkills)) * 100, 2) 
    //             : 0;

    //         $experienceScore = 0;
    //         foreach ($candidate['employment_history'] as $job) {
    //             $jobSkills = $job['skills_used'] ?? [];
    //             if (array_intersect($requiredSkills, $jobSkills)) {
    //                 $experienceScore++;
    //             }
    //         }
    //         $candidate['related_experience_score'] = $experienceScore * 10;

    //         $candidate['location_proximity_score'] = $this->getLocationProximityScore(
    //             strtolower($jobCriteria['students.location'] ?? ''),
    //             strtolower($candidate['address'] ?? '')
    //         );

    //         $rankedCandidates[] = $candidate;
    //     }

    //     return $this->rankCandidatesWithCohere($rankedCandidates, $jobCriteria, $apiKey);
    // }
    // public function getRankedCandidatesForJobOffer($jobCriteria, $allCandidates)
    // {
    //     $apiKey = 'ku4pOcnw7HIGqQkdDCxYYx5OCCULrjH041yny4ne'; // Replace with real key

    //     $filteredCandidates = [];
    //     $requiredSkills = isset($jobCriteria['students.skills']) 
    //         ? array_map('trim', explode(',', $jobCriteria['students.skills'])) 
    //         : [];
       
    //     foreach ($allCandidates as $candidate) {
    //         // --- Skill Score ---
    //         $candidateSkills = $candidate['skills_array'] ?? [];
    //         $matchedSkills = array_intersect($requiredSkills, $candidateSkills);
    //         $relatedSkillsScore = count($requiredSkills) > 0 
    //             ? round((count($matchedSkills) / count($requiredSkills)) * 100, 2) 
    //             : 0;

    //         // --- Experience Score ---
    //         $experienceScore = 0;
    //         foreach ($candidate['employment_history'] as $job) {
    //             $jobSkills = $job['skills_used'] ?? [];
    //             if (array_intersect($requiredSkills, $jobSkills)) {
    //                 $experienceScore++;
    //             }
    //         }
    //         $relatedExperienceScore = $experienceScore * 10;

    //         // --- Location Score ---
    //         $locationProximityScore = $this->getLocationProximityScore(
    //             strtolower($jobCriteria['students.location'] ?? ''),
    //             strtolower($candidate['address'] ?? '')
    //         );

    //         // --- Total Match Score ---
    //         $totalScore = round(($relatedSkillsScore + $relatedExperienceScore + $locationProximityScore) / 3, 2);

    //         // Attach scores
    //         $candidate['related_skills_score'] = $relatedSkillsScore;
    //         $candidate['related_experience_score'] = $relatedExperienceScore;
    //         $candidate['location_proximity_score'] = $locationProximityScore;
    //         $candidate['total_match_score'] = $totalScore;

    //         // Keep candidates with score ≥ 50
    //         if ($totalScore >= 50) {
    //             $filteredCandidates[] = $candidate;
    //         }

    //         // Stop early if we reach 10 valid candidates
    //         if (count($filteredCandidates) >= 10) {
    //             break;
    //         }
    //     }

      

    //     // ✅ Fallback: If no one hit 50%, include the best ones (at least 1)
    //     if (count($filteredCandidates) === 0 && count($allCandidates) > 0) {
    //         // Sort all by total score and take top 1 or more
    //         foreach ($allCandidates as &$candidate) {
    //             if (!isset($candidate['total_match_score'])) {
    //                 $candidate['total_match_score'] = 0; // Fallback value
    //             }
    //         }
    //         usort($allCandidates, fn($a, $b) => $b['total_match_score'] <=> $a['total_match_score']);
    //         $filteredCandidates[] = $allCandidates[0]; // Always include at least one best candidate
    //     }
      

    //     // Optional: Sort before sending to AI
    //     usort($filteredCandidates, fn($a, $b) => $b['total_match_score'] <=> $a['total_match_score']);

    //     return $this->rankCandidatesWithCohere($filteredCandidates, $jobCriteria, $apiKey);
    // }
    
    public function getRankedCandidatesForJobOffer($jobCriteria, $allCandidates)
    {
        $apiKey = 'ku4pOcnw7HIGqQkdDCxYYx5OCCULrjH041yny4ne'; 
        return $this->rankCandidatesWithCohere($allCandidates, $jobCriteria, $apiKey);
    }

    /**
     * Helper: check if candidate availability overlaps with job schedule
     */
    private function checkTimeOverlap($jobStart, $jobEnd, $candStart, $candEnd)
    {
        if (!$jobStart || !$jobEnd || !$candStart || !$candEnd) {
            return false;
        }

        $jobStartTime = strtotime($jobStart);
        $jobEndTime   = strtotime($jobEnd);
        $candStart    = strtotime($candStart);
        $candEnd      = strtotime($candEnd);

        return ($candStart <= $jobStartTime && $candEnd >= $jobEndTime);
    }




}
?>
