/* eslint-env jest */
import { FeatureFlags } from '../types';
import { MessageDescriptor } from 'react-intl';

export const $refType: any = null;
export const $fragmentRefs: any = null;

export const intlMock = {
    locale: 'fr-FR',
    formats: {},
    messages: {},
    now: () => 0,
    formatHTMLMessage: (message: MessageDescriptor) => String(message),
    formatPlural: (message: string) => String(message),
    formatNumber: (message: string) => String(message),
    formatRelative: (message: string) => String(message),
    formatTime: (message: string) => String(message),
    formatDate: (message: string) => String(message),
    formatMessage: (message: MessageDescriptor) => String(message.id),
};

export const features: FeatureFlags = {
    report_browers_errors_to_sentry: false,
    login_saml: false,
    login_cas: false,
    login_paris: false,
    oauth2_switch_user: false,
    votes_min: false,
    blog: false,
    calendar: false,
    login_facebook: false,
    privacy_policy: false,
    members_list: false,
    captcha: false,
    beta__admin_editor: false,
    beta__questionnaire_result: false,
    consent_external_communication: false,
    consent_internal_communication: false,
    newsletter: false,
    profiles: false,
    projects_form: false,
    project_trash: false,
    search: false,
    share_buttons: false,
    shield_mode: false,
    registration: false,
    phone_confirmation: false,
    reporting: false,
    themes: false,
    districts: false,
    user_type: false,
    votes_evolution: false,
    restrict_registration_via_email_domain: false,
    export: false,
    server_side_rendering: false,
    zipcode_at_register: false,
    consultation_plan: false,
    display_map: false,
    sso_by_pass_auth: false,
    allow_users_to_propose_events: false,
    secure_password: false,
    restrict_connection: false,
    login_franceconnect: false,
    read_more: false,
    display_pictures_in_depository_proposals_list: false,
    external_project: false,
    multilangue: false,
    display_pictures_in_event_list: false,
    beta__emailing: false,
    beta__emailing_parameters: false,
    proposal_revisions: false,
    unstable__tipsmeee: false,
    unstable__new_consultation_page: false,
    new_project_card: false,
    import_proposals: false,
    beta__analytics_page: false,
    http_redirects: false,
    unstable__project_admin: false,
    noindex_on_profiles: false,
    unstable__anonymous_questionnaire: false,
    twilio: false,
    developer_documentation: false,
    export_legacy_users: false,
    graphql_introspection: false,
    graphql_query_analytics: false,
    indexation: false,
    login_openid: false,
    public_api: false,
    remind_user_account_confirmation: false,
    sentry_log: false,
    unstable__paper_vote: false,
    unstable__secret_ballot: false,
    versions: false,
};

const environment = {
    UNSTABLE_getDefaultRenderPolicy: jest.fn(),
    __log: jest.fn(),
    getOperationTracker: jest.fn(),
    isRequestActive: jest.fn(),
    isServer: jest.fn(),
    options: jest.fn(),
    applyMutation: jest.fn(),
    sendMutation: jest.fn(),
    lookup: jest.fn(),
    sendQuery: jest.fn(),
    subscribe: jest.fn(),
    streamQuery: jest.fn(),
    retain: jest.fn(),
    unstable_internal: {
        areEqualSelectors: jest.fn(),
        createFragmentSpecResolver: jest.fn(),
        createOperationDescriptor: jest.fn(),
        getDataIDsFromFragment: jest.fn(),
        getDataIDsFromObject: jest.fn(),
        getFragment: jest.fn(),
        getPluralSelector: jest.fn(),
        getRequest: jest.fn(),
        getSelector: jest.fn(),
        getSelectorsFromObject: jest.fn(),
        getSingularSelector: jest.fn(),
        getVariablesFromFragment: jest.fn(),
        getVariablesFromObject: jest.fn(),
        getVariablesFromPluralFragment: jest.fn(),
        getVariablesFromSingularFragment: jest.fn(),
        isFragment: jest.fn(),
        isRequest: jest.fn(),
    },
    applyUpdate: jest.fn(),
    check: jest.fn(),
    commitPayload: jest.fn(),
    commitUpdate: jest.fn(),
    execute: jest.fn(),
    executeMutation: jest.fn(),
    executeWithSource: jest.fn(),
    getNetwork: jest.fn(),
    getStore: jest.fn(),
    areEqualSelectors: jest.fn(),
    createFragmentSpecResolver: jest.fn(),
    createOperationDescriptor: jest.fn(),
    getDataIDsFromFragment: jest.fn(),
    getDataIDsFromObject: jest.fn(),
    getFragment: jest.fn(),
    getPluralSelector: jest.fn(),
    getRequest: jest.fn(),
    getSelector: jest.fn(),
    getSelectorsFromObject: jest.fn(),
    getSingularSelector: jest.fn(),
    getVariablesFromFragment: jest.fn(),
    getVariablesFromObject: jest.fn(),
    getVariablesFromPluralFragment: jest.fn(),
    requiredFieldLogger: jest.fn(),
    revertUpdate: jest.fn(),
    replaceUpdate: jest.fn(),
    configName: 'test',
};

export const relayPaginationMock = {
    environment,
    hasMore: () => false,
    isLoading: () => false,
    loadMore: jest.fn(),
    refetchConnection: jest.fn(),
};

export const relayRefetchMock = {
    environment,
    refetch: jest.fn(),
};

export const googleAddressMock = {
    lat: 43.6424564,
    lng: -79.3755156,
    json: '[{"formatted_address":"10 Yonge St, Toronto, ON M5E 1R4, Canada","geometry":{"location":{"lat":43.6424564,"lng":-79.3755156},"location_type":"ROOFTOP","viewport":{"south":43.6411074197085,"west":-79.37686458029151,"north":43.6438053802915,"east":-79.37416661970849}},"types":["street_address"],"address_components":[{"long_name":"10","short_name":"10","types":["street_number"]},{"long_name":"Yonge Street","short_name":"Yonge St","types":["route"]},{"long_name":"Old Toronto","short_name":"Old Toronto","types":["political","sublocality","sublocality_level_1"]},{"long_name":"Toronto","short_name":"Toronto","types":["locality","political"]},{"long_name":"Toronto Division","short_name":"Toronto Division","types":["administrative_area_level_2","political"]},{"long_name":"Ontario","short_name":"ON","types":["administrative_area_level_1","political"]},{"long_name":"Canada","short_name":"CA","types":["country","political"]},{"long_name":"M5E 1R4","short_name":"M5E 1R4","types":["postal_code"]}],"place_id":"ChIJrbgtxSvL1IkR2xGe7JZyuw0","plus_code":{"compound_code":"JJRF+XQ Toronto, ON, Canada","global_code":"87M2JJRF+XQ"}}]',
};
