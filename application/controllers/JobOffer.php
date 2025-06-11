<?php
/**
 * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
 * @version 0.1.0
 */

class JobOffer extends MY_Controller {
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('JobOfferModel'); // Ensure you have created the JobOfferModel
        $this->load->model('StudentModel'); // Ensure you have created the StudentModel
        $this->load->model('EducationModel'); // Ensure you have created the EducationModel
        $this->load->model('ExperienceModel'); // Ensure you have created the ExperienceModel
        $this->load->model('NotificationModel'); // Ensure you have created the NotificationModel
        $this->load->model('StudentJobOfferModel'); // Ensure you have created the NotificationModel
        $this->load->model('EmployerModel'); // Ensure you have created the NotificationModel
        $this->load->model('UserActivityLogModel'); // Ensure you have created the NotificationModel
        $this->load->model('ContractModel'); // Ensure you have created the NotificationModel
        $this->load->library('Response', NULL, 'response');
        $this->load->library('EmailLib', NULL,'emaillib'); // Load your custom EmailLib library
    }

    public function checkToken() {
        $return = array(
            'isError' => false,
            'message' => 'Valid Token',
        );
        $this->response->output($return); // return the JSON encoded data
    }

    public function mysqlTQ($arrQuery) {
        $arrayIds = array();
        if (!empty($arrQuery)) {
            $this->db->trans_start();
            foreach ($arrQuery as $value) {
                $this->db->query($value);
                $last_id = $this->db->insert_id();
                array_push($arrayIds, $last_id);
            }
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
            } else {
                $this->db->trans_commit();
                return $arrayIds;
            }
        }
    }

    /**
     * Get all active job offers.
     */
    // public function get() {
    //     try {
    //         $headers = $this->input->request_headers();
    //         error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting
    
    //         // Retrieve the token from Authorization header
    //         $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
    //         if (strpos($token, 'Bearer ') === 0) {
    //             $token = substr($token, 7);
    //         }
    
    //         $decoded = decode_jwt($token, $this->config->item('jwt_key'));
    //         if (!empty($decoded->data->user_information)) {
    //             if ($decoded->data->user_type == "employer") {
    //                 $employer_id = $decoded->data->user_information[0]->employer_id;
    //             }
    //         }
    
    //         $id = $this->input->get("id");
    //         $is_active = $this->input->get("is_active");
    //         $payload = array(
    //             'job_offers.is_active' => isset($is_active) ? $is_active : 1
    //         );
    
    //         if (!empty($id)) {
    //             $payload['id'] = $id;
    //         }
    
    //         if (!empty($decoded->data->user_information)) {
    //             if ($decoded->data->user_type == "employer") {
    //                 $payload['job_offers.employer_id'] = $employer_id;
    //             }
    //         }
    
    //         // Fetch job offers
    //         $jobOffers = $this->JobOfferModel->get($payload);

    //         // Loop through each job offer and get matching candidates
    //         if ($decoded->data->user_type == "employer") {
    //             foreach ($jobOffers as &$jobOffer) {
    //                 // Define the criteria for candidate matching (based on skills, availability, etc.)
    //                 $candidatePayload = array(
    //                     'students.skills' => $jobOffer->skills,
    //                     'students.employment_type' => $jobOffer->employment_type,
    //                     'students.location' => $jobOffer->location,
    //                     'students.is_active' => 1,
    //                 );
                
    //                 // Fetch candidates that match the job offer criteria
    //                 $candidates = $this->JobOfferModel->getMatchingCandidates($candidatePayload);
                
    //                 foreach ($candidates as &$candidate) {
    //                     // Fetch education
    //                     $education_payload = array(
    //                         'education.student_id' => $candidate['student_id']
    //                     );
    //                     $education = $this->EducationModel->get($education_payload);
                    
    //                     // Fetch experience
    //                     $experience_payload = array(
    //                         'experience.student_id' => $candidate['student_id']
    //                     );
    //                     $experience = $this->ExperienceModel->get($experience_payload);
                    
    //                     // Attach data directly to candidate object
    //                     $candidate['education'] = $education;
    //                     $candidate['experience'] = $experience;
    //                 }
                
    //                 // Attach enriched candidates to the job offer
    //                 $jobOffer->candidates = $candidates;
    //             }
    //         }
            
    //         // Prepare the response
    //         $return = array(
    //             'isError' => false,
    //             'message' => 'Success',
    //             'data' => $jobOffers,
    //         );
    //                 } catch (Exception $e) {
    //         $return = array(
    //             'isError' => true,
    //             'message' => $e->getMessage(),
    //         );
    //     }
    //     $this->response->output($return); // Return the JSON encoded data
    // }

    public function getold() {
        try {
            $headers = $this->input->request_headers();
            error_log(print_r($headers, true)); // Debugging: Log headers
    
            // Get token
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
    
            $decoded = decode_jwt($token, $this->config->item('jwt_key'));
            $userType = $decoded->data->user_type ?? null;
            $userInfo = $decoded->data->user_information[0] ?? null;
    
            $id = $this->input->get("id");
            $is_active = $this->input->get("is_active");
    
            $payload = [
                'job_offers.is_active' => isset($is_active) ? $is_active : 1
            ];
    
            if (!empty($id)) {
                $payload['id'] = $id;
            }
    
            if ($userType === "employer" && !empty($userInfo->employer_id)) {
                $payload['job_offers.employer_id'] = $userInfo->employer_id;
            }
    
            // Fetch job offers
            $jobOffers = $this->JobOfferModel->get($payload);
   
    
            if ($userType === "employer" || $userType === "admin") {
                foreach ($jobOffers as &$jobOffer) {
                    $candidatePayload = [
                        'students.skills' => $jobOffer->skills,
                        'students.employment_type' => $jobOffer->employment_type,
                        'students.location' => $jobOffer->location,
                        'students.is_active' => 1,
                    ];
    
                    $candidates = $this->JobOfferModel->getMatchingCandidates($candidatePayload);
    
                    foreach ($candidates as &$candidate) {
                        $education = $this->EducationModel->get(['education.student_id' => $candidate['student_id']]);
                        $experience = $this->ExperienceModel->get(['experience.student_id' => $candidate['student_id']]);
                        $sentOfferIds = $this->StudentJobOfferModel->hasBeenSent($candidate['student_id'], [$jobOffer->job_offers_id]);
                        $offerData = $this->StudentJobOfferModel->getOfferStatus($candidate['student_id'], $jobOffer->job_offers_id);
                        $candidate['education'] = $education;
                        $candidate['experience'] = $experience;
                        $candidate['has_job_offer'] = in_array($jobOffer->job_offers_id, $sentOfferIds);
                        $candidate['job_offer_status'] = $offerData['status'] ?? null;
                        $contract = $this->ContractModel->get(array(
                            'job_offers_id' => $jobOffer->job_offers_id,
                            'student_id' => $candidate['student_id'],
                        ), true); // 'true' to return only the first/latest row if your model supports it
                 
                        $candidate['contract'] = $contract ? $contract[0]->pdf_path : null;
                        $candidate['contract_status'] = $contract ? $contract[0]->status : null;

                    }
    
                    $jobOffer->candidates = $candidates;
                }
    
            } elseif ($userType === "student" && !empty($userInfo->student_id)) {
                // Get student info
                $this->db->select('students.*, courses.courses');
                $this->db->from('students');
                $this->db->join('courses', 'students.course_id = courses.course_id', 'left');
                $this->db->where('students.student_id', $userInfo->student_id);
                $student = $this->db->get()->row_array();
    
                if (!$student) {
                    throw new Exception("Student not found");
                }
    
                // Get all active job offers
         
                $jobOffers = $this->JobOfferModel->get([
                    'job_offers.is_active' => 1
                ],'array');
    
                // Rank job offers using Cohere
                $apiKey = 'ku4pOcnw7HIGqQkdDCxYYx5OCCULrjH041yny4ne'; // Replace with secure storage
                $rankedOffers = $this->JobOfferModel->rankJobOffersWithCohere($student, $jobOffers, $apiKey);
                
              

                // Add has_been_sent flag to each job offer
                foreach ($rankedOffers as &$offer) {
                    $sentOfferIds = $this->StudentJobOfferModel->hasBeenSent($userInfo->student_id, [$offer['job_offers_id']]);
                    $sentOffer = $this->StudentJobOfferModel->getOfferStatus($userInfo->student_id, $offer['job_offers_id']);

                    $offer['has_job_offer'] = in_array($offer['job_offers_id'], $sentOfferIds);
                    $offer['job_offer_status'] = $sentOffer['status'] ?? null; // e.g., 'sent', 'accepted', 'declined'
                    $contract = $this->ContractModel->get(array(
                        'job_offers_id' => $offer['job_offers_id'],
                        'student_id' => $userInfo->student_id,
                    ), true); // 'true' to return only the first/latest row if your model supports it
             
                    $offer['contract'] = $contract ? $contract[0]->pdf_path : null;
                }


                // Overwrite $jobOffers for student with personalized ranked ones
                $jobOffers = $rankedOffers;
            }
    
            $return = [
                'isError' => false,
                'message' => 'Success',
                'data' => $jobOffers,
            ];
        } catch (Exception $e) {
            $return = [
                'isError' => true,
                'message' => $e->getMessage(),
            ];
        }
    
        $this->response->output($return);
    }
    
    public function get()
    {
        try {
            $headers = $this->input->request_headers();
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;

            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }

            $decoded = decode_jwt($token, $this->config->item('jwt_key'));
            $userType = $decoded->data->user_type ?? null;
            $userInfo = $decoded->data->user_information[0] ?? null;

            $id = $this->input->get("id");
            $is_active = $this->input->get("is_active");

            $payload = [
                'job_offers.is_active' => isset($is_active) ? $is_active : 1
            ];

            if (!empty($id)) {
                $payload['id'] = $id;
            }

            if ($userType === "employer" && !empty($userInfo->employer_id)) {
                $payload['job_offers.employer_id'] = $userInfo->employer_id;
            }

            // Get job offers
            $jobOffers = $this->JobOfferModel->get($payload);

            // EMPLOYER OR ADMIN VIEW
            if ($userType === "employer" || $userType === "admin") {
                // 🔁 Fetch all candidates once
                $allCandidates = $this->JobOfferModel->getAllActiveCandidates();

                foreach ($jobOffers as &$jobOffer) {
                    $criteria = [
                        'students.skills' => $jobOffer->skills,
                        'students.employment_type' => $jobOffer->employment_type,
                        'students.location' => $jobOffer->location
                    ];

                    // ⚙️ Use refactored function to score/reuse candidates
                    $candidates = $this->JobOfferModel->getRankedCandidatesForJobOffer($criteria, $allCandidates);

                    foreach ($candidates as &$candidate) {
                        $education = $this->EducationModel->get(['education.student_id' => $candidate['student_id']]);
                        $experience = $this->ExperienceModel->get(['experience.student_id' => $candidate['student_id']]);
                        $sentOfferIds = $this->StudentJobOfferModel->hasBeenSent($candidate['student_id'], [$jobOffer->job_offers_id]);
                        $offerData = $this->StudentJobOfferModel->getOfferStatus($candidate['student_id'], $jobOffer->job_offers_id);

                        $candidate['education'] = $education;
                        $candidate['experience'] = $experience;
                        $candidate['has_job_offer'] = in_array($jobOffer->job_offers_id, $sentOfferIds);
                        $candidate['job_offer_status'] = $offerData['status'] ?? null;

                        $contract = $this->ContractModel->get([
                            'job_offers_id' => $jobOffer->job_offers_id,
                            'student_id' => $candidate['student_id']
                        ], true);

                        $candidate['contract'] = $contract ? $contract[0]->pdf_path : null;
                        $candidate['contract_status'] = $contract ? $contract[0]->status : null;
                    }

                    $jobOffer->candidates = $candidates;
                }

            }
            // STUDENT VIEW
            elseif ($userType === "student" && !empty($userInfo->student_id)) {
                $this->db->select('students.*, courses.courses');
                $this->db->from('students');
                $this->db->join('courses', 'students.course_id = courses.course_id', 'left');
                $this->db->where('students.student_id', $userInfo->student_id);
                $student = $this->db->get()->row_array();

                if (!$student) {
                    throw new Exception("Student not found");
                }

                $jobOffers = $this->JobOfferModel->get([
                    'job_offers.is_active' => 1
                ], 'array');

                // AI RANKING (student to job offers)
                $apiKey = 'ku4pOcnw7HIGqQkdDCxYYx5OCCULrjH041yny4ne';
                $rankedOffers = $this->JobOfferModel->rankJobOffersWithCohere($student, $jobOffers, $apiKey);
                
                foreach ($rankedOffers as &$offer) {
                    $sentOfferIds = $this->StudentJobOfferModel->hasBeenSent($userInfo->student_id, [$offer['job_offers_id']]);
                    $sentOffer = $this->StudentJobOfferModel->getOfferStatus($userInfo->student_id, $offer['job_offers_id']);

                    $offer['has_job_offer'] = in_array($offer['job_offers_id'], $sentOfferIds);
                    $offer['job_offer_status'] = $sentOffer['status'] ?? null;

                    $contract = $this->ContractModel->get([
                        'job_offers_id' => $offer['job_offers_id'],
                        'student_id' => $userInfo->student_id
                    ], true);

                    $offer['contract'] = $contract ? $contract[0]->pdf_path : null;
                }

                $jobOffers = $rankedOffers; // replace output for student
            }

            $this->response->output([
                'isError' => false,
                'message' => 'Success',
                'data' => $jobOffers,
            ]);
        } catch (Exception $e) {
            $this->response->output([
                'isError' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }


    public function create() {
        $transQuery = array();
        // Retrieve form data for the job offer.
        $data = json_decode(file_get_contents("php://input"), true);
   
        $job_title = $data['job_title'] ?? '';
        $skills = $data['skills'] ?? '';
        $location = $data['location'] ?? '';
        $min_salary = $data['min_salary'] ?? '';
        $max_salary = $data['max_salary'] ?? '';
        $company_id = $data['company_id'] ?? '';
        $expired_at = $data['expired_at'] ?? '';
        $job_description = $data['job_description'] ?? '';
        $employment_type = $data['employment_type'] ?? '';
        $company_overview = $data['company_overview'] ?? '';
        $qualifications = $data['qualifications'] ?? '';
        $employer_id = "";
        $user_id = "";

        $headers = $this->input->request_headers();
        error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting

        // Retrieve the token from Authorization header
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;


        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));

        if(!empty($decoded->data->user_information)){
            $employer_id = $decoded->data->user_information[0]->employer_id;
            $user_id = $decoded->data->user_information[0]->user_id;
        }




        // Validation checks for job offer data.
        if (empty($job_title)) {
            $return = array(
                'isError' => true,
                'message' => 'Job title is required',
            );
        }else if (empty($job_description)) {
            $return = array(
                'isError' => true,
                'message' => 'Job description are required',
            );
        }
        else if (empty($skills)) {
            $return = array(
                'isError' => true,
                'message' => 'Skills are required',
            );
        } else if (empty($location)) {
            $return = array(
                'isError' => true,
                'message' => 'Location is required',
            );
        } else if (empty($min_salary)) {
            $return = array(
                'isError' => true,
                'message' => 'Min Salary is required',
            );
        }else if (empty($max_salary)) {
            $return = array(
                'isError' => true,
                'message' => 'Max Salary is required',
            );
        } else if (empty($employer_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Employer ID is required',
            );
        } else if (empty($company_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Company ID is required',
            );
        } else if (empty($expired_at)) {
            $return = array(
                'isError' => true,
                'message' => 'Expiration date is required',
            );
        }
        else if (empty($employment_type)) {
            $return = array(
                'isError' => true,
                'message' => 'Employment type is required',
            );
        }
        else if (empty($company_overview)) {
            $return = array(
                'isError' => true,
                'message' => 'Company Overview is required',
            );
        }
        else if (empty($qualifications)) {
            $return = array(
                'isError' => true,
                'message' => 'Qualification is required',
            );
        }
        else {
            try {
                // Prepare the payload for inserting the job offer into the database.
                $payload = array(
                    'job_title' => $job_title,
                    'skills' => $skills,
                    'location' => $location,
                    'job_description' => $job_description,
                    'min_salary' => $min_salary,
                    'max_salary' => $max_salary,
                    'employer_id' => $employer_id,
                    'company_id' => $company_id,
                    'date_added' => date('Y-m-d'),
                    'expired_at' => $expired_at,
                    'employment_type' => $employment_type,
                    'company_overview' => $company_overview,
                    'qualifications' => $qualifications,
                );

                // Call the model's add method to insert the job offer into the database.
                $job_offer_query = $this->JobOfferModel->add($payload);
                array_push($transQuery, $job_offer_query);

                $user_activity_data = array(
                    'user_id' => $user_id,
                    'activity_type' => 'job_offer',
                    'activity_details' => "User {$decoded->data->lastname} {$decoded->data->firstname} added new job offer $job_title for the date of ".date("Y-m-d H:i:s"),
                    'related_id' => $employer_id,
                    'related_table' => 'job_offers',
                    'created_at' => date("Y-m-d H:i:s")
                );

                $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
                array_push($transQuery, $user_activity_query);

                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);
                // Check if the insert was successful.
                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully created job offer',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error creating job offer',
                    );
                }
            } catch (Exception $e) {
                // Handle any exceptions and return an error response.
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }

        // Output the response (JSON-encoded data).
        $this->response->output($return);
    }

    public function update() {
        $transQuery = array();

        // Retrieve form data for job offer
        $job_offers_id = $this->input->post('job_offers_id');
        $job_title = $this->input->post('job_title');
        $skills = $this->input->post('skills');
        $job_description = $this->input->post('job_description');
        $location = $this->input->post('location');
        $min_salary = $this->input->post('min_salary');
        $max_salary = $this->input->post('max_salary');
        $expired_at = $this->input->post('expired_at');
        $status = $this->input->post('status');
        $employment_type = $this->input->post('employment_type');

        // Validation checks for job offer data
        if (empty($job_offers_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Job Offer ID is required',
            );
        } else if (empty($job_title)) {
            $return = array(
                'isError' => true,
                'message' => 'Job title is required',
            );
        } else if (empty($job_description)) {
            $return = array(
                'isError' => true,
                'message' => 'Job Description is required',
            );
        } 
        else if (empty($skills)) {
            $return = array(
                'isError' => true,
                'message' => 'Skills are required',
            );
        } else if (empty($min_salary)) {
            $return = array(
                'isError' => true,
                'message' => 'Min Salary is required',
            );
        } else if (empty($max_salary)) {
            $return = array(
                'isError' => true,
                'message' => 'Max Salary is required',
            );
        }else if (empty($location)) {
            $return = array(
                'isError' => true,
                'message' => 'Location is required',
            );
        } else if (empty($status)) {
            $return = array(
                'isError' => true,
                'message' => 'Status is required',
            );
        } else if (empty($employment_type)) {
            $return = array(
                'isError' => true,
                'message' => 'Employment type is required',
            );
        }else {
            try {
                // Payload array for job offer data to be updated
                $payload = array(
                    'job_title' => $job_title,
                    'skills' => $skills,
                    'location' => $location,
                    'min_salary' => $min_salary,
                    'max_salary' => $max_salary,
                    'job_description' => $job_description,
                    'expired_at' => $expired_at,
                    'status' => $status,
                    'updated_at' => date("Y-m-d"),
                    'employment_type' => $employment_type
                );

                // The condition to find the job offer record to update
                $where = array(
                    'job_offers_id' => $job_offers_id
                );

                // Call model function to update the job offer
                $response = $this->JobOfferModel->update($payload, $where);
                array_push($transQuery, $response);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                // Success response
                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully updated job offer',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error updating job offer',
                    );
                }

            } catch (Exception $e) {
                // Handle exception and return error response
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }

        // Output the response
        $this->response->output($return);
    }

    public function void() {
        $transQuery = array();

        // Retrieve form data
        $job_offers_id = $this->input->post('job_offers_id');

        // Validation checks
        if (empty($job_offers_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Job Offer ID is required',
            );
        } else {
            try {
                // Payload array for updating the job offer to soft-delete it
                $payload = array(
                    'is_active' => 0,
                    'deleted_at' => date("Y-m-d"),
                );

                $where = array(
                    'job_offers_id' => $job_offers_id
                );

                // Call model function to update job offer status
                $response = $this->JobOfferModel->update($payload, $where);
                array_push($transQuery, $response);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                // Success response
                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully void job offer',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error void job offer',
                    );
                }

            } catch (Exception $e) {
                // Handle exception and return error response
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }

        // Output the response
        $this->response->output($return);
    }

    public function activate() {
        $transQuery = array();

        // Retrieve form data
        $job_offers_id = $this->input->post('job_offers_id');

        // Validation checks
        if (empty($job_offers_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Job Offer ID is required',
            );
        } else {
            try {
                // Payload array for updating the job offer to activate it
                $payload = array(
                    'is_active' => 1,
                    'deleted_at' => null,
                    'updated_at' => date("Y-m-d"),
                );

                $where = array(
                    'job_offers_id' => $job_offers_id
                );

                // Call model function to update job offer status
                $response = $this->JobOfferModel->update($payload, $where);
                array_push($transQuery, $response);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                // Success response
                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully activated job offer',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error activating job offer',
                    );
                }

            } catch (Exception $e) {
                // Handle exception and return error response
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }

        // Output the response
        $this->response->output($return);
    }

    public function sendOffer()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $student_id = $data['student_id'] ?? '';
        $job_offer_id = $data['job_offer_id'] ?? '';
        $pdf_path = $data['pdf_path'] ?? '';

        if (empty($student_id) || empty($job_offer_id)) {
            $return = [
                'isError' => true,
                'message' => 'Student ID and Job Offer ID are required',
            ];
            $this->response->output($return);
            return;
        }
        if (empty($pdf_path)) {
            $return = [
                'isError' => true,
                'message' => 'Please Upload Contact',
            ];
            $this->response->output($return);
            return;
        }

        // Decode JWT for user_id
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));
        $user_id = $decoded->data->user_information[0]->user_id ?? null;

        try {
            // Check if already sent
            $exists = $this->StudentJobOfferModel->check_if_exists($student_id, $job_offer_id);
            if ($exists) {
                $return = [
                    'isError' => true,
                    'message' => 'Job offer already sent to this student.',
                ];
                $this->response->output($return);
                return;
            }

            // Add job offer
            $payload = [
                'student_id' => $student_id,
                'job_offer_id' => $job_offer_id,
                'date_offered' => date('Y-m-d H:i:s'),
                'status' => 'pending',
            ];
            $inserted = $this->StudentJobOfferModel->add($payload);

            if (!$inserted) {
                throw new Exception('Failed to insert job offer record.');
            }




            // Fetch student and job offer details
            $student = $this->StudentModel->getStudentById($student_id);
            $jobOffer = $this->JobOfferModel->getJobOfferById($job_offer_id);

            if (!$student || !$jobOffer) {
                throw new Exception('Student or Job Offer not found.');
            }


           

            
          

            // Compose email content
            $recipientEmail = $student->email;
            $user = "{$student->firstname} {$student->middlename} {$student->lastname}";
            $job_title = $jobOffer->job_title;
            $job_description = $jobOffer->job_description;
            $company_overview = $jobOffer->company_overview;
            $skills = $jobOffer->skills;
            $company_name = $jobOffer->company_name;
            $company_address = $jobOffer->company_address;
            $company_contact_number = $jobOffer->company_contact_number;
            $company_email = $jobOffer->company_email;

            $body = "Dear {$user},<br><br>
                We are excited to inform you that you have received a new job offer!<br><br>
                <strong>Position:</strong> {$job_title} <br>
                <strong>Company:</strong> {$company_name} <br>
                <strong>Date:</strong> " . date('F j, Y') . "<br><br>
                Job Description: {$job_description} <br><br>
                Company Overview: {$company_overview} <br><br>
                Skills Required: {$skills} <br><br>
                Best regards,<br>
                {$company_name}<br>
                {$company_address}<br>
                {$company_contact_number}<br>
                {$company_email}";

            // Send email
            

             //upload contact
             $payload = array(
                'job_offers_id'   => $job_offer_id ?? '',
                'student_id'      => $student_id ?? '',
                'contract_title'  => "Contact of $user for $job_title in $company_name",
                'pdf_path'        => $pdf_path ?? '',
                'signature'       => '',
                'signed_at'       => date("Y-m-d H:i:s"),
            );

            $contact_response = $this->ContractModel->add($payload);



            $emailSent = $this->emaillib->sendEmail($body, $recipientEmail, "Job Offer Notification");

            if ($emailSent) {
                
                $notificationPayload = [
                    'receive_by' => $student->user_id,
                    'created_by' => $user_id,
                    'company_id' => $jobOffer->company_id,
                    'title' => "Job Offer for {$user}",
                    'message' => $body,
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->NotificationModel->add($notificationPayload);


                $user_activity_data = array(
                    'user_id' => $user_id,
                    'activity_type' => 'job_offer',
                    'activity_details' => "User {$decoded->data->lastname} {$decoded->data->firstname} send job offer $job_title to $user for the date of ".date("Y-m-d H:i:s"),
                    'related_id' => $inserted,
                    'related_table' => 'job_offers',
                    'created_at' => date("Y-m-d H:i:s")
                );
    
                $this->UserActivityLogModel->direct_add($user_activity_data);

                $return = [
                    'isError' => false,
                    'message' => 'Job offer sent and email delivered successfully.',
                    'data' => $payload
                ];
            } else {
                $return = [
                    'isError' => false,
                    'message' => 'Job offer recorded, but email failed to send.',
                    'data' => $payload
                ];
            }
        } catch (Exception $e) {
            $return = [
                'isError' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->response->output($return);
    }

    public function acceptJobOffer()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            $student_id = $data['student_id'] ?? null;
            $job_offer_id = $data['job_offer_id'] ?? null;
            $base64_image = $data['base64_image'] ?? null;
            $status = "accepted";
    
            if (empty($student_id) || empty($job_offer_id)) {
                $this->response->output([
                    'isError' => true,
                    'message' => 'Student ID and Job Offer ID are required.'
                ]);
                return;
            }
            if (empty($base64_image)) {
                $this->response->output([
                    'isError' => true,
                    'message' => 'Base 64 image are required.'
                ]);
                return;
            }
    
            $payload = [
                'status' => $status,
                'date_responded' => date('Y-m-d H:i:s')
            ];
    
            $updated = $this->StudentJobOfferModel->updateByStudentAndOffer($student_id, $job_offer_id, $payload);
            

            $contact_payload = array(
                'pdf_path'        => $base64_image ?? '',
                'status'        =>  'signed',
                'signed_at'       => date("Y-m-d H:i:s"),
            );
            $contract_where = array(
                'job_offers_id'   => $job_offer_id,
                'student_id'      => $student_id,
            );

            $this->ContractModel->update_direct($contact_payload,$contract_where);

            if ($updated) {
                // Fetch job offer
                $jobOffer = $this->JobOfferModel->getJobOfferById($job_offer_id);
                // Fetch employer info
                $employer = $this->EmployerModel->getById($jobOffer->employer_id);
                // Fetch student info
                $student = $this->StudentModel->getStudentById($student_id);
                $studentName = "{$student->firstname} {$student->lastname}";

                
    
                $body = "Dear {$employer->firstname},<br><br>
                    The student <strong>{$studentName}</strong> has <strong>ACCEPTED</strong> your job offer for the position of <strong>{$jobOffer->job_title}</strong>.<br><br>
                    Thank you for using our platform.<br><br>
                    - Job Buddy Team";
    
                $this->emaillib->sendEmail($body, $employer->email, "Job Offer Accepted");

                $notificationPayload = [
                    'receive_by' => $employer->user_id,
                    'created_by' => $student->user_id,
                    'company_id' => $jobOffer->company_id,
                    'title' => "Accepted Job Offer {$studentName}",
                    'message' => $body,
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->NotificationModel->add($notificationPayload);


                $user_activity_data = array(
                    'user_id' => $student->user_id,
                    'activity_type' => 'job_offer',
                    'activity_details' => "User {$studentName} accepted the job offer $jobOffer->job_title from  employer $employer->firstname for the date of ".date("Y-m-d H:i:s"),
                    'related_id' => $job_offer_id,
                    'related_table' => 'job_offers',
                    'created_at' => date("Y-m-d H:i:s")
                );
    
                $this->UserActivityLogModel->direct_add($user_activity_data);

    
                $this->response->output([
                    'isError' => false,
                    'message' => 'Offer status updated and email sent to employer.'
                ]);
            } else {
                $this->response->output([
                    'isError' => true,
                    'message' => 'No matching offer found or no changes made.'
                ]);
            }
    
        } catch (Exception $e) {
            $this->response->output([
                'isError' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function rejectJobOffer()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            
            $student_id = $data['student_id'] ?? null;
            $job_offer_id = $data['job_offer_id'] ?? null;
            $status = "rejected";

            if (empty($student_id) || empty($job_offer_id)) {
                $this->response->output([
                    'isError' => true,
                    'message' => 'Student ID and Job Offer ID are required.'
                ]);
                return;
            }

            $payload = [
                'status' => $status,
                'date_responded' => date('Y-m-d H:i:s')
            ];

            $updated = $this->StudentJobOfferModel->updateByStudentAndOffer($student_id, $job_offer_id, $payload);

            if ($updated) {
                // Fetch job offer
                $jobOffer = $this->JobOfferModel->getJobOfferById($job_offer_id);
                // Fetch employer info
                $employer = $this->EmployerModel->getById($jobOffer->employer_id);
                // Fetch student info
                $student = $this->StudentModel->getStudentById($student_id);
                $studentName = "{$student->firstname} {$student->lastname}";

                $body = "Dear {$employer->firstname},<br><br>
                    The student <strong>{$studentName}</strong> has <strong>REJECTED</strong> your job offer for the position of <strong>{$jobOffer->job_title}</strong>.<br><br>
                    You may consider reaching out to other candidates.<br><br>
                    - Job Buddy Team";

                $this->emaillib->sendEmail($body, $employer->email, "Job Offer Rejected");

                $notificationPayload = [
                    'receive_by' => $employer->user_id,
                    'created_by' => $student->user_id,
                    'company_id' => $jobOffer->company_id,
                    'title' => "REJECTED Job Offer {$studentName}",
                    'message' => $body,
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->NotificationModel->add($notificationPayload);

                $user_activity_data = array(
                    'user_id' => $student->user_id,
                    'activity_type' => 'job_offer',
                    'activity_details' => "User {$studentName} REJECTED the job offer $jobOffer->job_title from  employer $employer->firstname for the date of ".date("Y-m-d H:i:s"),
                    'related_id' => $job_offer_id,
                    'related_table' => 'job_offers',
                    'created_at' => date("Y-m-d H:i:s")
                );
    
                $this->UserActivityLogModel->direct_add($user_activity_data);

                $this->response->output([
                    'isError' => false,
                    'message' => 'Offer status updated and email sent to employer.'
                ]);
            } else {
                $this->response->output([
                    'isError' => true,
                    'message' => 'No matching offer found or no changes made.'
                ]);
            }

        } catch (Exception $e) {
            $this->response->output([
                'isError' => true,
                'message' => $e->getMessage()
            ]);
        }
    }






    // public function sendOffer() {
    //     $data = json_decode(file_get_contents("php://input"), true);
    //     $student_id = $data['student_id'] ?? '';
    //     $job_offer_id = $data['job_offer_id'] ?? '';
    
    //     if (empty($student_id) || empty($job_offer_id)) {
    //         $return = array(
    //             'isError' => true,
    //             'message' => 'Student ID and Job Offer ID are required',
    //         );
    //     } else {
    //         try {
    //             // Check if offer already exists
    //             $exists = $this->StudentJobOfferModel->check_if_exists($student_id, $job_offer_id);
    //             if ($exists) {
    //                 $return = array(
    //                     'isError' => true,
    //                     'message' => 'Job offer already sent to this student.',
    //                 );
    //             } else {
    //                 $payload = array(
    //                     'student_id' => $student_id,
    //                     'job_offer_id' => $job_offer_id,
    //                     'date_offered' => date('Y-m-d H:i:s'),
    //                     'status' => 'sent',
    //                 );
    
    //                 $inserted = $this->StudentJobOfferModel->add($payload);
    //                 if ($inserted) {
    //                     $return = array(
    //                         'isError' => false,
    //                         'message' => 'Job offer successfully sent',
    //                         'data' => $payload
    //                     );
    //                 } else {
    //                     $return = array(
    //                         'isError' => true,
    //                         'message' => 'Failed to send job offer',
    //                     );
    //                 }
    //             }
    //         } catch (Exception $e) {
    //             $return = array(
    //                 'isError' => true,
    //                 'message' => $e->getMessage(),
    //             );
    //         }
    //     }
    
    //     $this->response->output($return);
    // }

    // public function sendEmailOffer()
    // {

    //     $data = json_decode(file_get_contents("php://input"), true);

    //     $headers = $this->input->request_headers();
    //     $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
    //     if (strpos($token, 'Bearer ') === 0) {
    //         $token = substr($token, 7);
    //     }
    
    //     $decoded = decode_jwt($token, $this->config->item('jwt_key'));

    //     if (!empty($decoded->data->user_information)) {
    //         $user_id = $decoded->data->user_information[0]->user_id;
    //     }

    //     $student_id = $data['student_id'] ?? '';
    //     $job_offer_id = $data['job_offer_id'] ?? '';
    //     $student = $this->StudentModel->getStudentById($student_id);
    //     if (empty($student_id) || empty($job_offer_id)) {

          

    //         $return = [
    //             'isError' => true,
    //             'message' => 'Student ID and Job Offer ID are required',
    //             'date' => date('Y-m-d')
    //         ];
    //         $this->response->output($return);
    //         return;
    //     }


    //     // Fetch student data

        
    //     // Fetch job offer data
    //     $jobOffer = $this->JobOfferModel->getJobOfferById($job_offer_id);
  
    //     if (!$student || !$jobOffer) {
    //         $return = [
    //             'isError' => true,
    //             'message' => 'Student or Job Offer not found',
    //             'date' => date('Y-m-d')
    //         ];
    //         $this->response->output($return);
    //         return;
    //     }

    //     $recipientEmail = $student->email;
    //     $user = "{$student->firstname} {$student->middlename} {$student->lastname}";
    //     $position = $jobOffer->job_title;
    //     $job_description = $jobOffer->job_description;
    //     $company_overview = $jobOffer->company_overview;
    //     $skills = $jobOffer->skills;
    //     $company_name = $jobOffer->company_name;
    //     $company_address = $jobOffer->company_address;
    //     $company_contact_number = $jobOffer->company_contact_number;
    //     $company_email = $jobOffer->company_email;

    //     $body = "Dear {$user},<br><br>

    //         We are excited to inform you that you have received a new job offer!<br><br>

    //         <strong>Position:</strong> {$position} <br>
    //         <strong>Company:</strong> $company_name. <br>
    //         <strong>Date:</strong> " . date('F j, Y') . "<br><br>

    //         Please review the offer details and respond at your earliest convenience. We look forward to having you on board!<br><br>
            
    //         Job Description:
    //         $job_description <br><br>

    //         Company Overview:
    //         $company_overview <br><br>

    //         Skills Required:
    //         $skills <br><br>

    //         Best regards,<br>
    //         $company_name<br>
    //         $company_address<br>
    //         $company_contact_number<br>
    //         $company_email.";

    //     // Call your custom EmailLib send() method
    //     $emailSent = $this->emaillib->sendEmail($body, $recipientEmail, "Job Offer Notification");

    //     if ($emailSent) {

    //         $payload = [
    //             'receive_by' => $student->user_id,
    //             'created_by' => $user_id,
    //             'company_id' => $jobOffer->company_id,
    //             'title' => "Job Offer for $user",
    //             'message' => $body,
    //             'is_read' => 0,
    //             'created_at' => date('Y-m-d H:i:s')
    //         ];

    //         $this->NotificationModel->add($payload);

    //         $return = [
    //             'isError' => false,
    //             'message' => 'Successfully sent Email',
    //             'date' => date('Y-m-d')
    //         ];
    //     } else {
    //         $return = [
    //             'isError' => true,
    //             'message' => 'Error sending Email',
    //             'date' => date('Y-m-d')
    //         ];
    //     }
    //     $this->response->output($return);
    // }

}
?>
