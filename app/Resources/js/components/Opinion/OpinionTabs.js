import OpinionArgumentForm from './OpinionArgumentForm';
import OpinionSourceForm from './OpinionSourceForm';
import OpinionSourceList from './OpinionSourceList';
import OpinionArgumentList from './OpinionArgumentList';
import Fetcher from '../../services/Fetcher';

const TabbedArea = ReactBootstrap.TabbedArea;
const TabPane = ReactBootstrap.TabPane;
const Row = ReactBootstrap.Row;
const Col = ReactBootstrap.Col;
const FormattedMessage = ReactIntl.FormattedMessage;

const OpinionTabs = React.createClass({
  propTypes: {
    opinion: React.PropTypes.object.isRequired,
  },
  mixins: [ReactIntl.IntlMixin],

  getInitialState() {
    return {
      categories: [],
    };
  },

  componentDidMount() {
    Fetcher
    .get('/categories')
    .then((data) => {
      this.setState({categories: data});
      return true;
    });
  },

  renderArgumentsContent() {
    return (
      <Row>
        <Col sm={12} md={6}>
          <div className="opinion opinion--add-argument block block--bordered">
            <OpinionArgumentForm type="yes" opinion={this.props.opinion}/>
          </div>
          <OpinionArgumentList type="yes" opinion={this.props.opinion}/>
        </Col>
        <Col sm={12} md={6}>
          <div className="opinion opinion--add-argument block block--bordered">
            <OpinionArgumentForm type="no" opinion={this.props.opinion}/>
          </div>
          <OpinionArgumentList type="no" opinion={this.props.opinion}/>
        </Col>
      </Row>
    );
  },

  render() {
    const opinion = this.props.opinion;
    if (this.isSourceable()) {
      return (
        <TabbedArea defaultActiveKey={1} animation={false}>
          <TabPane className="opinion-tabs" eventKey={1} tab={
            <FormattedMessage message={this.getIntlMessage('global.arguments')} num={opinion.arguments_count} />
          }>
            {this.renderArgumentsContent()}
          </TabPane>
          <TabPane className="opinion-tabs" eventKey={2} tab={
            <FormattedMessage message={this.getIntlMessage('global.sources')} num={opinion.sources_count} />
          }>
            <br />
            <OpinionSourceForm categories={this.state.categories} opinion={this.props.opinion}/>
            <OpinionSourceList sources={this.props.opinion.sources}/>
          </TabPane>
        </TabbedArea>
      );
    }
    return this.renderArgumentsContent();
  },

  isSourceable() {
    const type = this.props.opinion.parent ? this.props.opinion.parent.type : this.props.opinion.type;
    if (type !== 'undefined') {
      return type.sourceable;
    }
    return false;
  },

});

export default OpinionTabs;
