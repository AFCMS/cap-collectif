import OpinionAppendix from './OpinionAppendix';

const OpinionAppendices = React.createClass({
  propTypes: {
    opinion: React.PropTypes.object.isRequired,
  },
  mixins: [ReactIntl.IntlMixin],

  render() {
    if (!this.hasAppendices()) {
      return null;
    }
    const opinion = this.props.opinion;
    const appendices = this.isVersion() ? opinion.parent.appendices : opinion.appendices;

    return (
      <div className="opinion__description">
        {this.isVersion()
          ?
          <p>
            {this.getIntlMessage('opinion.version_parent')}
            <a href={opinion.parent._links.show} >{opinion.parent.title}</a>
          </p>
          : null
        }
        {
          appendices.map((appendix, index) => {
            if (appendix.body) {
              return (
                <OpinionAppendix appendix={appendix} expanded={index === 0} />
              );
            }
          })
        }
      </div>
    );
  },

  isVersion() {
    return this.props.opinion.parent ? true : false;
  },

  hasAppendices() {
    const appendices = this.isVersion() ? this.props.opinion.parent.appendices : this.props.opinion.appendices;
    if (!appendices) {
      return false;
    }
    return appendices.some( (app) => {
      if (app.body) {
        return true;
      }
      return false;
    });
  },

});

export default OpinionAppendices;


