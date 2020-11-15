<?php
/**
 * Rest API example class
 */


class DT_Questionnaire_Plugin_Endpoints
{
    public $permissions = [ 'access_contacts', 'access_groups' ];

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }


    //See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
    public function add_api_routes() {
        $namespace = 'dt-questionnaire/v1';

        register_rest_route(
            $namespace, "download", [
                "methods"  => "GET",
                "callback" => [ $this, "download_endpoint" ]
            ]
        );

        register_rest_route(
            $namespace, "questionnaires/(?P<id>\d+)", [
                "methods"  => "GET",
                "callback" => [ $this, "questionnaire_endpoint" ]
            ]
        );

        register_rest_route(
            $namespace, "questionnaires", [
                "methods" => "GET",
                "callback" => [ $this, "questionnaires_endpoint" ]
            ]
        );

        register_rest_route(
            $namespace, "submit", [
                "methods"  => "POST",
                "callback" => [ $this, "submit_endpoint" ]
            ]
        );
    }

    public function download_endpoint( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $since = null;
        if ( isset( $request['since'] ) ) {
          $since = $request['since'];
        }
        return DT_Questionnaire_Plugin::get_instance()->get_questionnaire_responses_since( $since );
    }

    public function questionnaire_endpoint( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $params = $request->get_params();
        if (! isset( $params['id'] ) ) {
            return new WP_Error( __FUNCTION__, "Unable to process questionnaire request", [ 'status' => 500 ] );
        }
        return DT_Questionnaire_Plugin::get_instance()->get_questionnaire_by_id( $params['id'] );
    }

    public function questionnaires_endpoint( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $include_inactive = FALSE;
        if (isset($request['inactive']) && is_bool($request['inactive'])) {
            $include_inactive = $request['inactive'];
        }
        return DT_Questionnaire_Plugin::get_instance()->get_questionnaires($include_inactive);
    }

    public function submit_endpoint( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $params = $request->get_params();
        if ( !isset( $params['fields'] ) ) {
            return new WP_Error( __FUNCTION__, "missing fields param", [ 'status' => 401 ] );
        }
        $fields = $params['fields'];
        return DT_Questionnaire_Plugin::get_instance()->submit_questionnaire_response( $fields );
    }
}
/**
 * Initialize instance
 */
DT_Questionnaire_Plugin_Endpoints::instance();
