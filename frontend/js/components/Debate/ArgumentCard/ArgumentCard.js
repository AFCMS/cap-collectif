// @flow
import * as React from 'react';
import { truncate } from 'lodash';
import { createFragmentContainer, graphql } from 'react-relay';
import moment from 'moment';
import { FormattedMessage, type IntlShape, useIntl } from 'react-intl';
import type { ArgumentCard_argument } from '~relay/ArgumentCard_argument.graphql';
import type { ArgumentCard_viewer } from '~relay/ArgumentCard_viewer.graphql';
import Flex from '~ui/Primitives/Layout/Flex';
import Card from '~ds/Card/Card';
import Tag from '~ds/Tag/Tag';
import Text from '~ui/Primitives/Text';
import Heading from '~ui/Primitives/Heading';
import ButtonQuickAction from '~ds/ButtonQuickAction/ButtonQuickAction';
import LoginOverlay from '~/components/Utils/LoginOverlay';
import { mutationErrorToast } from '~/components/Utils/MutationErrorToast';
import AddDebateArgumentVoteMutation from '~/mutations/AddDebateArgumentVoteMutation';
import RemoveDebateArgumentVoteMutation from '~/mutations/RemoveDebateArgumentVoteMutation';
import Button from '~ds/Button/Button';
import type { AppBoxProps } from '~ui/Primitives/AppBox.type';
import { ICON_NAME, ICON_SIZE } from '~ds/Icon/Icon';
import Menu from '../../DesignSystem/Menu/Menu';

export type DebateOpinionStatus = 'FOR' | 'AGAINST';

type Props = {|
  ...AppBoxProps,
  +onReadMore?: () => void,
  +argument: ArgumentCard_argument,
  +viewer: ?ArgumentCard_viewer,
  +isMobile?: boolean,
  +setReportModalId: (id: string) => void,
  +setModerateModalId: (id: string) => void,
|};

export const voteForArgument = (
  debateArgumentId: string,
  viewerHasVote: ?boolean,
  intl: IntlShape,
) => {
  if (viewerHasVote)
    return RemoveDebateArgumentVoteMutation.commit({ input: { debateArgumentId } })
      .then(response => {
        if (response.removeDebateArgumentVote?.errorCode) {
          mutationErrorToast(intl);
        }
      })
      .catch(() => {
        mutationErrorToast(intl);
      });
  return AddDebateArgumentVoteMutation.commit({ input: { debateArgumentId } })
    .then(response => {
      if (response.addDebateArgumentVote?.errorCode) {
        mutationErrorToast(intl);
      }
    })
    .catch(() => {
      mutationErrorToast(intl);
    });
};

const getTruncatedLength = (isMobile?: boolean): number => {
  if (isMobile) return 230;
  return 450;
};
export const ArgumentCard = ({
  argument,
  viewer,
  isMobile,
  onReadMore,
  setModerateModalId,
  setReportModalId,
  ...props
}: Props) => {
  const intl = useIntl();
  const isViewerAdmin = viewer && viewer.isAdmin;

  return (
    <Card p={6} bg="white" {...props}>
      <Flex direction="column">
        <Flex mb={2} direction="row" justifyContent="space-between" alignItems="center">
          <Flex direction="row" alignItems="center">
            <Text maxWidth="100px" mr={2}>
              {argument.author.username}
            </Text>
            <Tag variant={argument.type === 'FOR' ? 'green' : 'red'}>
              <Heading as="h5" fontWeight="700" uppercase>
                <FormattedMessage id={argument.type === 'FOR' ? 'global.for' : 'global.against'} />
              </Heading>
            </Tag>
          </Flex>

          <Flex direction="row" align="center">
            <Text color="neutral-gray.500">
              {moment(argument.publishedAt)
                .startOf('day')
                .fromNow()}
            </Text>

            {isViewerAdmin && (
              <Button
                onClick={() => setModerateModalId(argument.id)}
                rightIcon={ICON_NAME.MODERATE}
                color="blue.900"
                p={0}
              />
            )}

            {viewer && !isViewerAdmin && !argument.viewerHasReport && (
              <Menu>
                <Menu.Button as={React.Fragment}>
                  <Button
                    rightIcon={ICON_NAME.MORE}
                    aria-label={intl.formatMessage({ id: 'global.menu' })}
                    p={0}
                    color="blue.900"
                  />
                </Menu.Button>
                <Menu.List>
                  <Menu.ListItem>
                    <Button
                      onClick={() => setReportModalId(argument.id)}
                      leftIcon={ICON_NAME.FLAG}
                      color="blue.900">
                      {intl.formatMessage({ id: 'global.report.submit' })}
                    </Button>
                  </Menu.ListItem>
                </Menu.List>
              </Menu>
            )}
          </Flex>
        </Flex>

        <Text>
          {truncate(argument.body, { length: getTruncatedLength(isMobile) })}{' '}
          {getTruncatedLength(isMobile) < argument.body.length && (
            <>
              &nbsp;
              <Button display="inline-block" onClick={onReadMore} variant="link">
                <FormattedMessage id="capco.module.read_more" />
              </Button>
            </>
          )}
        </Text>
        <Flex mt={3} alignItems="center" flexDirection="row">
          <LoginOverlay>
            <ButtonQuickAction
              onClick={() => voteForArgument(argument.id, argument.viewerHasVote, intl)}
              mr={1}
              size={isMobile ? ICON_SIZE.LG : ICON_SIZE.MD}
              icon="THUMB_UP"
              variantColor="green"
              label={
                <FormattedMessage id={argument.viewerHasVote ? 'global.cancel' : 'vote.add'} />
              }
              iconColor={argument.viewerHasVote ? 'green.500' : 'gray.500'}
            />
          </LoginOverlay>
          <Text as="span" fontSize={3} color="gray.500">
            {argument.votes.totalCount}
          </Text>
        </Flex>
      </Flex>
    </Card>
  );
};

export default createFragmentContainer(ArgumentCard, {
  argument: graphql`
    fragment ArgumentCard_argument on DebateArgument
      @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
      id
      body
      votes(first: 0) {
        totalCount
      }
      author {
        username
      }
      type
      publishedAt
      viewerHasReport @include(if: $isAuthenticated)
      viewerHasVote @include(if: $isAuthenticated)
    }
  `,
  viewer: graphql`
    fragment ArgumentCard_viewer on User {
      isAdmin
    }
  `,
});
