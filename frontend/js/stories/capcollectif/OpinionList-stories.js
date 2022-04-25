// @flow
import * as React from 'react';
import { boolean, select, text } from 'storybook-addon-knobs';
import { storiesOf } from '@storybook/react';
import { Button, ListGroupItem, Tooltip, OverlayTrigger, Popover, Label } from 'react-bootstrap';
import { UserAvatarDeprecated } from '../../components/User/UserAvatarDeprecated';
import InlineList from '../../components/Ui/List/InlineList';
import ListGroup from '../../components/Ui/List/ListGroup';
import Media from '../../components/Ui/Medias/Media/Media';
import Card from '../../components/Ui/Card/Card';
import PieChart from '../../components/Ui/Chart/PieChart';
import PinnedLabel from '../../components/Utils/PinnedLabel';

import { opinions as opinionsMock } from '../mocks/opinions';

const headerOption = {
  Gray: 'gray',
  White: 'white',
  Green: 'green',
  BlueDark: 'bluedark',
  Blue: 'blue',
  Orange: 'orange',
  Red: 'red',
  Default: 'default',
};

// TODO: Split this to make more UI components
const OpinionItem = ({ item, typeLabel }) => (
  <React.Fragment>
    <Media>
      <Media.Left>
        <UserAvatarDeprecated user={item.user} />
      </Media.Left>
      <Media.Body className="opinion__body">
        <div className="opinion__user">
          {item.user && (
            <a href="https://ui.cap-collectif.com" className="excerpt_dark">
              {item.user.username}
            </a>
          )}
          {!item.user && <span>Utilisateur supprimé</span>}
          <span className="excerpt small" title={item.createdAt}>
            {' • '} {item.createdAt}
          </span>
          {item.updatedAt && (
            <span className="excerpt small">
              {' • '}
              <OverlayTrigger
                placement="top"
                overlay={
                  <Tooltip placement="top" id="tooltip-top">
                    Modifiée le 15/03/2015
                  </Tooltip>
                }>
                <span>Modifiée</span>
              </OverlayTrigger>
            </span>
          )}
          <PinnedLabel show={item.pinned || false} type="opinion" />
          {item.ranking && (
            <span className="text-label text-label--green ml-10">
              <i className="cap cap-trophy" />
              {item.ranking}
            </span>
          )}
          {!item.published && (
            <React.Fragment>
              {' '}
              <OverlayTrigger
                placement="top"
                overlay={
                  <Popover
                    id="popover-id"
                    title={
                      <strong className="excerpt_dark">Compte en attente de confirmation</strong>
                    }>
                    <p>
                      Votre opinion n’a pas été publié, car votre compte a été confirmé après la
                      date de fin de l’étape.
                    </p>
                  </Popover>
                }>
                <Label bsStyle="danger" bsSize="xs">
                  <i className="cap cap-delete-2" /> Non comptabilisé
                </Label>
              </OverlayTrigger>
            </React.Fragment>
          )}
          {typeLabel && (
            <React.Fragment>
              {' '}
              <Label>{typeLabel}</Label>
            </React.Fragment>
          )}
        </div>
        {item.trashedStatus === 'INVISIBLE' ? (
          <div>[Contenu masqué]</div>
        ) : (
          <Card.Title tagName="div" firstElement={false}>
            <a href={item.url}>{item.title}</a>
          </Card.Title>
        )}
        <InlineList className="excerpt small" separator="•">
          <li>{`${item.votes.totalCount} votes`}</li>
          <li>{`${item.versions.totalCount} amendements`}</li>
          <li>{`${item.arguments.totalCount} arguments`}</li>
          <li>{`${item.sources.totalCount} source`}</li>
        </InlineList>
      </Media.Body>
    </Media>
    <div className="hidden-xs">
      {item.votes.totalCount > 0 && (
        <PieChart
          data={[
            { name: "D'accord", value: item.votesOk.totalCount },
            { name: 'Mitigé', value: item.votesMitige.totalCount },
            { name: "Pas d'accord", value: item.votesNok.totalCount },
          ]}
          colors={['#5cb85c', '#f0ad4e', '#d9534f']}
        />
      )}
    </div>
  </React.Fragment>
);

const OpinionList = ({ section, opinions }) => (
  <Card id="opinions--test17" className="anchor-offset" style={{ border: 0 }}>
    <Card.Header bgColor={section.bgColor}>
      <div className="opinion d-flex align-items-center justify-content-between">
        <strong className="excerpt_dark">{`${opinions.length} propositions`}</strong>
        <div className="d-flex align-items-center justify-content-between">
          {opinions.length > 1 && (
            <form className="form-inline">
              <select
                defaultChecked="positions"
                className="form-control"
                aria-label="Trier"
                onChange={() => {}}
                onBlur={() => {}}>
                <option value="positions">Tri ordonné puis aléatoire</option>
                <option value="random">Aléatoire</option>
                <option value="last">Les plus récents</option>
                <option value="old">Les plus anciens</option>
                <option value="favorable">Les plus favorables</option>
                <option value="votes">Les plus votés</option>
                <option value="comments">Les plus commentés</option>
              </select>
            </form>
          )}
          <Button
            bsStyle="primary"
            id="btn-add--"
            onClick={() => {}}
            className="m-0"
            disable={section.contribuable}>
            <i className="cap cap-add-1" />
            <span className="hidden-xs"> Nouvelle proposition</span>
          </Button>
        </div>
      </div>
    </Card.Header>
    {opinions.length === 0 ? (
      <ListGroupItem className="text-center excerpt">
        <i className="cap-32 cap-baloon-1" />
        <br />
        Aucune proposition
      </ListGroupItem>
    ) : (
      <ListGroup className="m-0">
        {opinions.map((item, index) => (
          <ListGroupItem
            key={index}
            className={`list-group-item__opinion opinion text-left has-chart${
              item.user && item.user.vip ? ' bg-vip' : ''
            }`}
            style={{ backgroundColor: item.user && item.user.vip ? '#F7F7F7' : undefined }}>
            <OpinionItem item={item} typeLabel={section.typeLabel || null} />
          </ListGroupItem>
        ))}
        {section.paginationEnable && (
          <ListGroupItem>
            <Button block componentClass="a" bsStyle="link" href="https://ui.cap-collectif.com">
              Voir toutes les propositions
            </Button>
          </ListGroupItem>
        )}
      </ListGroup>
    )}
  </Card>
);

storiesOf('Cap Collectif|OpinionList', module)
  .add('default case', () => {
    const section = {
      contribuable: boolean('Contribuable', true, 'Section'),
      bgColor: select('Header background color', headerOption, 'default', 'Section'),
      paginationEnable: boolean('Pagination enabled', true, 'Section'),
    };

    return <OpinionList section={section} opinions={opinionsMock} />;
  })
  .add('with single opinion', () => {
    const section = {
      contribuable: boolean('Contribuable', true, 'Section'),
      bgColor: select('Header background color', headerOption, 'default', 'Section'),
      paginationEnable: boolean('Pagination enabled', false, 'Section'),
    };

    return <OpinionList section={section} opinions={[opinionsMock[0]]} />;
  })
  .add('empty', () => {
    const section = {
      contribuable: boolean('Contribuable', true, 'Section'),
      bgColor: select('Header background color', headerOption, 'default', 'Section'),
      paginationEnable: boolean('Pagination enabled', true, 'Section'),
    };

    return <OpinionList section={section} opinions={[]} />;
  })
  .add('in trash', () => {
    const section = {
      contribuable: boolean('Contribuable', true, 'Section'),
      bgColor: select('Header background color', headerOption, 'default', 'Section'),
      typeLabel: text('Type label', 'Dans la corbeille', 'Section'),
      paginationEnable: boolean('Pagination enabled', true, 'Section'),
    };

    return <OpinionList section={section} opinions={opinionsMock} />;
  });
