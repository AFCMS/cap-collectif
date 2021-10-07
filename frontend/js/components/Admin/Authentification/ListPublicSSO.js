// @flow
import React, { useState } from 'react';
import { Button, ListGroupItem } from 'react-bootstrap';
import { connect } from 'react-redux';
import { FormattedMessage } from 'react-intl';
import styled, { type StyledComponent } from 'styled-components';
import { createFragmentContainer, graphql } from 'react-relay';
import Toggle from '~/components/Ui/Toggle/Toggle';
import ListGroup from '../../Ui/List/ListGroup';
import type { Dispatch, FeatureToggle, FeatureToggles, State as GlobalState } from '~/types';
import { toggleFeature } from '~/redux/modules/default';
import { toggleStatus } from '~/mutations/ToggleSSOConfigurationStatusMutation';
import FranceConnectConfigurationModal from './FranceConnectConfigurationModal';
import type { ListPublicSSO_ssoConfigurations } from '~relay/ListPublicSSO_ssoConfigurations.graphql';
import type { FranceConnectConfigurationModal_ssoConfiguration$ref } from '~relay/FranceConnectConfigurationModal_ssoConfiguration.graphql';
import FranceConnectTeaserModal from '~/components/Admin/Authentification/FranceConnectTeaserModal';
import AppBox from '~ui/Primitives/AppBox';
import FacebookConfigurationCard from '~/components/Admin/Authentification/FacebookConfigurationCard';
import type { FacebookConfigurationModal_ssoConfiguration$ref } from '~relay/FacebookConfigurationModal_ssoConfiguration.graphql';

type Props = {|
  ssoConfigurations: ListPublicSSO_ssoConfigurations,
  features: FeatureToggles,
  onToggle: (feature: FeatureToggle, value: boolean) => void,
|};

type SSOConfiguration = {|
  +__typename: string,
  +id: string,
  +enabled: boolean,
  +clientId: ?string,
  +secret: ?string,
|};

export type FranceConnectSSOConfiguration = {|
  ...SSOConfiguration,
  +$fragmentRefs: FranceConnectConfigurationModal_ssoConfiguration$ref,
|};

export type FacebookSSOConfiguration = {|
  ...SSOConfiguration,
  +$fragmentRefs: FacebookConfigurationModal_ssoConfiguration$ref,
|};

const ListGroupItemWithJustifyContentStart: StyledComponent<{}, {}, typeof ListGroupItem> = styled(
  ListGroupItem,
)`
  && {
    justify-content: start;
  }

  .form-group {
    margin-bottom: 0;
    margin-top: 5px;
  }
`;

const ButtonWithMarginLeftAuto: StyledComponent<{}, {}, typeof Button> = styled(Button)`
  && {
    margin-left: auto;
  }
`;

export const ListPublicSSO = ({ features, ssoConfigurations }: Props) => {
  const [showFranceConnectModal, setShowFranceConnectModal] = useState<boolean>(false);

  const handleClose = () => {
    setShowFranceConnectModal(false);
  };

  const franceConnect =
    ssoConfigurations.edges &&
    ssoConfigurations.edges
      .filter(Boolean)
      .map(edge => edge.node)
      .filter(Boolean)
      .find(node => node.__typename === 'FranceConnectSSOConfiguration');

  const facebook =
    ssoConfigurations.edges &&
    ssoConfigurations.edges
      .filter(Boolean)
      .map(edge => edge.node)
      .filter(Boolean)
      .find(node => node.__typename === 'FacebookSSOConfiguration');

  return (
    <>
      <ListGroup>
        <ListGroupItemWithJustifyContentStart>
          <Toggle
            id="toggle-franceConnect"
            checked={features.login_franceconnect && franceConnect?.enabled}
            onChange={() => toggleStatus(franceConnect)}
            label={
              <h5 className="mb-0 mt-0">
                <FormattedMessage id="capco.module.login_franceconnect" />
              </h5>
            }
          />

          {features.login_franceconnect && franceConnect?.enabled && (
            <ButtonWithMarginLeftAuto
              bsStyle="warning"
              className="btn-outline-warning"
              onClick={() => {
                setShowFranceConnectModal(!showFranceConnectModal);
              }}>
              <i className="fa fa-pencil" /> <FormattedMessage id="global.edit" />
            </ButtonWithMarginLeftAuto>
          )}

          {!features.login_franceconnect && (
            <AppBox marginLeft="auto">
              <FranceConnectTeaserModal />
            </AppBox>
          )}

          <FranceConnectConfigurationModal
            show={showFranceConnectModal}
            onClose={handleClose}
            ssoConfiguration={franceConnect}
          />
        </ListGroupItemWithJustifyContentStart>
        {facebook && (
          <ListGroupItemWithJustifyContentStart>
            <FacebookConfigurationCard ssoConfiguration={facebook} />
          </ListGroupItemWithJustifyContentStart>
        )}
        <ListGroupItemWithJustifyContentStart>
          <Toggle
            id="toggle-email"
            checked
            disabled
            label={
              <h5 className="mb-0 mt-0">
                <FormattedMessage id="global.email" />
              </h5>
            }
          />
        </ListGroupItemWithJustifyContentStart>
      </ListGroup>
    </>
  );
};

const mapStateToProps = (state: GlobalState) => ({
  features: state.default.features,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  onToggle: (feature: FeatureToggle, value: boolean) => {
    toggleFeature(dispatch, feature, value);
  },
});

export default connect<any, any, _, _, _, _>(
  mapStateToProps,
  mapDispatchToProps,
)(
  createFragmentContainer(ListPublicSSO, {
    ssoConfigurations: graphql`
      fragment ListPublicSSO_ssoConfigurations on SSOConfigurationConnection {
        edges {
          node {
            ... on SSOConfiguration {
              id
              __typename
              enabled
              ...FranceConnectConfigurationModal_ssoConfiguration
              ...FacebookConfigurationCard_ssoConfiguration
            }
          }
        }
      }
    `,
  }),
);
